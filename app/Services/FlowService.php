<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\ExamType;
use App\Models\Order;
use App\Models\Prescription;
use App\Models\GatewayTransaction;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FlowService
{
    protected $apiKey;
    protected $secretKey;
    protected $urlBase;

    public function __construct()
    {
        $this->apiKey = config('services.flow.api_key');
        $this->secretKey = config('services.flow.secret_key');
        $this->urlBase = config('services.flow.environment') === 'production'
            ? 'https://www.flow.cl/api'
            : 'https://sandbox.flow.cl/api';
    }

    /**
     * 1. LANZADOR: Crea el intento de pago.
     * Ya no recibe $extraData por parámetro, usa los datos del modelo Order.
     */

    public function createPayment(Order $order)
{
    $endpoint = $this->urlBase . '/payment/create';

    // Generamos un identificador único para este intento de pago
    // Ejemplo: ORD-019ced7c-A2B1
    $buyOrder = "ORD-" . substr($order->id, 0, 8) . "-" . strtoupper(bin2hex(random_bytes(2)));

    // Usamos firstOrCreate para evitar el error Integrity constraint violation (Duplicate entry)
    // Si ya existe una transacción 'pending' para esta orden, la recuperamos.
    // Si no, la creamos con el nuevo buy_order.
    $gatewayTrx = GatewayTransaction::firstOrCreate(
        [
            'payable_type' => get_class($order),
            'payable_id'   => $order->id,
            'status'       => 'pending'
        ],
        [
            'user_id'      => auth()->id(),
            'gateway'      => 'flow',
            'buy_order'    => $buyOrder,
            'amount'       => (int)$order->amount,
            'raw_response' => [
                'exam_type_id' => $order->exam_type_id,
                'type'         => $order->type
            ]
        ]
    );

    // Si la recuperó de la DB pero el buy_order es distinto, usamos el de la DB para Flow
    $currentBuyOrder = $gatewayTrx->buy_order;

    $params = [
        'apiKey'          => $this->apiKey,
        'commerceOrder'   => $currentBuyOrder,
        'subject'         => "Pago Orden #" . $order->id,
        'amount'          => (int)$order->amount,
        'email'           => auth()->user()->email,
        'urlConfirmation' => route('flow.webhook'),
        'urlReturn'       => route('flow.return'),
    ];

    $params['s'] = $this->makeSignature($params);

    try {
        $response = Http::asForm()->post($endpoint, $params);

        if ($response->successful()) {
            $res = $response->object();
            // Guardamos el token que nos da Flow para identificarlo en el retorno/webhook
            $gatewayTrx->update(['token' => $res->token]);
            return $res;
        }

        Log::error("Flow API Error: " . $response->body());
        return null;
    } catch (\Exception $e) {
        Log::error("Excepción en Flow createPayment: " . $e->getMessage());
        return null;
    }
}

    /**
     * 2. PROCESADOR: El corazón del Webhook.
     * Aquí unificamos la lógica comercial y médica.
     */
    public function handleWebhook($token)
    {
        Log::info("=== [FLOW WEBHOOK START] ===", ['token' => $token]);

        try {
            // A. Consultar estado real en Flow
            $statusResponse = $this->getPaymentStatus($token);
            if (!$statusResponse || $statusResponse->status != 2) {
                Log::warning("Pago no autorizado en Flow", (array)$statusResponse);
                return false;
            }

            // B. Recuperar registros locales (GatewayTrx y Order)
            $gatewayTrx = GatewayTransaction::where('token', $token)->first();
            if (!$gatewayTrx) throw new \Exception("Transacción técnica no encontrada.");

            $order = Order::findOrFail($gatewayTrx->payable_id);

            // C. TRANSACCIÓN ATÓMICA
            return DB::transaction(function () use ($order, $gatewayTrx, $statusResponse, $token) {

                // 1. Actualizar estado técnico
                $gatewayTrx->update([
                    'status' => 'authorized',
                    'raw_response' => json_encode($statusResponse)
                ]);

                // 2. Registro Contable (Transaction)
                $this->registerTransaction($gatewayTrx, $order, $token, $statusResponse);

                // 3. Lógica según tipo de Orden (Standard / Otros)
                if ($order->type === 'standard' || $order->type === 'medical_purchase') {
                    $this->processMedicalOrder($order);
                } else {
                    $order->update(['status' => 'paid']);
                }

                Log::info("=== [FLOW WEBHOOK SUCCESS] ===", ['order_id' => $order->id]);
                return true;
            });

        } catch (\Exception $e) {
            Log::error("ERROR FATAL WEBHOOK: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lógica específica para generar recetas médicas tras el pago.
     */
    private function processMedicalOrder(Order $order)
    {
        $exam = ExamType::findOrFail($order->exam_type_id);
        $doctor = Doctor::getNextAvailableForSpecialty($exam->specialty_id);

        if (!$doctor) throw new \Exception("No hay médicos disponibles.");

        $prescription = Prescription::create([
            'id'                => (string) Str::uuid(),
            'order_id'          => $order->id,
            'doctor_id'         => $doctor->id,
            'exam_type_id'      => $exam->id,
            'status'            => 'active',
            'verification_code' => strtoupper(Str::random(8)),
        ]);

        $doctor->update(['last_assigned_at' => now()]);

        // Intentar firma automática
        try {
            $signatureService = app(\App\Services\SignatureService::class);
            $signatureResult = $signatureService->sign($prescription);

        if ($signatureResult && $signatureResult->success) {
            // IMPORTANTE: Asegúrate de usar comillas
            $prescription->update(['status' => 'signed']);
            $order->update(['status' => 'paid']);
        } else {
            Log::warning("Firma fallida, la orden queda en revisión manual.");
            $order->update(['status' => 'manual_review']);
        }
        } catch (\Exception $e) {
            Log::error("Error en firma: " . $e->getMessage());
            $order->update(['status' => 'manual_review']);
        }
    }

    /**
     * 3. CONTABILIDAD: Registro universal de ingresos.
     */
    private function registerTransaction($gatewayTrx, $order, $token, $statusResponse)
    {
        return Transaction::create([
            'sender_id'      => $gatewayTrx->user_id,
            'receiver_id'    => null,
            'reference_id'   => $order->id,
            'reference_code' => $gatewayTrx->buy_order,
            'amount'         => $gatewayTrx->amount,
            'type'           => $order->type ?? 'medical_purchase',
            'status'         => 'completed',
            'metadata'       => [
                'gateway'        => 'flow',
                'flow_token'     => $token,
                'payment_method' => $statusResponse->paymentData->method ?? 'unknown',
            ]
        ]);
    }

    /**
     * HELPERS TÉCNICOS
     */
    public function getPaymentStatus(string $token)
    {
        $params = ['apiKey' => $this->apiKey, 'token' => $token];
        $params['s'] = $this->makeSignature($params);
        $response = Http::get($this->urlBase . '/payment/getStatus', $params);
        return $response->successful() ? $response->object() : null;
    }

    protected function makeSignature(array $params): string
    {
        ksort($params);
        $toSign = "";
        foreach ($params as $key => $value) {
            $toSign .= $key . $value;
        }
        return hash_hmac('sha256', $toSign, $this->secretKey);
    }
}
