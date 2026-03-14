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
     * Crea el intento de pago en Flow vinculándolo a la Orden Comercial.
     */
    public function createPayment(Order $order, array $extraData = [])
    {
        $endpoint = $this->urlBase . '/payment/create';
        $buyOrder = "COM-" . strtoupper(bin2hex(random_bytes(4)));

        // 1. Registro en pasarela con metadata para el Webhook
        $gatewayTrx = GatewayTransaction::create([
            'user_id'      => auth()->id(),
            'gateway'      => 'flow',
            'buy_order'    => $buyOrder,
            'amount'       => (int)$order->amount,
            'status'       => 'pending',
            'payable_type' => get_class($order),
            'payable_id'   => $order->id,
            'metadata'     => [
                'exam_type_id' => $extraData['exam_type_id'] ?? null,
                'type'         => $extraData['type'] ?? 'standard',
            ]
        ]);

        $params = [
            'apiKey'          => $this->apiKey,
            'commerceOrder'   => $buyOrder,
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
     * Procesa la confirmación de pago (Webhook).
     */

    // En app/Services/FlowService.php


    public function handleWebhook($token)
{
    Log::info("WEBHOOK: Recibido token $token");

    // 1. Obtener datos del pago desde Flow
    $statusResponse = $this->getPaymentData($token); // Asumo que este método devuelve el objeto/array de Flow

    // 2. Encontrar la Orden Comercial y la Transacción de Pasarela
    $order = Order::findOrFail($statusResponse['commerceOrder']);
    $gatewayTrx = GatewayTransaction::where('token', $token)->firstOrFail();

    // 3. Extraer Metadata
    $examTypeId = $statusResponse['optional']['exam_type_id'] ?? null;
    $orderType = $statusResponse['optional']['type'] ?? 'standard';

    return DB::transaction(function () use ($order, $gatewayTrx, $statusResponse, $token, $examTypeId, $orderType) {

        // ACTUALIZAR TRANSACCIÓN DE PASARELA
        $gatewayTrx->update([
            'status' => 'authorized',
            'raw_response' => json_encode($statusResponse)
        ]);

        // REGISTRAR MOVIMIENTO CONTABLE INTERNO
        $this->registerTransaction($gatewayTrx, $order, $token, (object)$statusResponse);

        if ($orderType === 'standard') {
            Log::info("WEBHOOK: Flujo STANDARD");

            $exam = ExamType::findOrFail($examTypeId);
            $doctor = Doctor::getNextAvailableForSpecialty($exam->specialty_id);

            if (!$doctor) {
                throw new \Exception("No hay médicos disponibles.");
            }

            $prescription = Prescription::create([
                'id' => (string) Str::uuid(),
                'order_id' => $order->id,
                'doctor_id' => $doctor->id,
                'exam_type_id' => $exam->id,
                'status' => 'active',
                'verification_code' => strtoupper(Str::random(8)),
            ]);

            $doctor->update(['last_assigned_at' => now()]);

            // FIRMA
            try {
                $signatureService = app(\App\Services\SignatureService::class);
                $signatureResult = $signatureService->sign($prescription);

                if ($signatureResult->success) {
                    $order->update(['status' => 'completed']);
                    Log::info("WEBHOOK: Proceso y Contabilidad completados.");
                } else {
                    throw new \Exception("Fallo en firma.");
                }
            } catch (\Exception $e) {
                Log::error("WEBHOOK: Error en firma: " . $e->getMessage());
                $order->update(['status' => 'failed']);
                throw $e;
            }

        } else {
            // Flujo CUSTOM
            Prescription::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'order_id' => $order->id,
                'status' => 'pending',
                'verification_code' => strtoupper(\Illuminate\Support\Str::random(8)),
            ]);

            $order->update(['status' => 'completed']);
        }

        return true;
    });
}


    /**
     * Registra el movimiento contable interno.
     */
    private function registerTransaction($gatewayTrx, $order, $token, $status) {
        Transaction::create([
            'sender_id'      => $gatewayTrx->user_id,
            'receiver_id'    => null, // Se puede actualizar cuando el médico firme o tome la orden
            'reference_id'   => $order->id,
            'reference_code' => $gatewayTrx->buy_order,
            'amount'         => $gatewayTrx->amount,
            'type'           => 'medical_purchase',
            'status'         => 'completed',
            'metadata'       => [
                'gateway' => 'flow',
                'flow_token' => $token,
                'payment_method' => $status->paymentMethod ?? 'unknown'
            ]
        ]);
    }

    /**
     * Solicita el reembolso a Flow.
     */
    public function requestRefund(Order $order, GatewayTransaction $gatewayTrx, $flowTrxId = null)
    {
        $endpoint = $this->urlBase . '/refund/create';
        $refundOrder = "REF-" . strtoupper(bin2hex(random_bytes(4)));

        $params = [
            'apiKey'              => $this->apiKey,
            'refundCommerceOrder' => $refundOrder,
            'receiverEmail'       => $order->patient->user->email,
            'amount'              => (int)$order->amount,
            'urlCallBack'         => route('flow.refund.webhook'),
        ];

        if ($flowTrxId) {
            $params['flowTrxId'] = $flowTrxId;
        } else {
            $params['commerceTrxId'] = $gatewayTrx->buy_order;
        }

        $params['s'] = $this->makeSignature($params);

        try {
            $response = Http::asForm()->post($endpoint, $params);
            if ($response->successful()) {
                $res = $response->object();
                $order->update([
                    'status' => 'refund_pending',
                    'internal_notes' => $order->internal_notes . "\n[Reembolso Flow Token: {$res->token}]"
                ]);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error("Error en reembolso: " . $e->getMessage());
            return false;
        }
    }

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








    // /**
    //  * Crea el intento de pago en Flow y lo registra en la BD
    //  */
    // public function createPayment(MedicalOrder $order)
    // {
    //     $endpoint = $this->urlBase . '/payment/create';
    //     $buyOrder = "MED-" . strtoupper(bin2hex(random_bytes(4)));

    //     $gatewayTrx = GatewayTransaction::create([
    //         'user_id' => auth()->id(),
    //         'gateway' => 'flow',
    //         'buy_order' => $buyOrder,
    //         'amount' => (int)$order->amount,
    //         'status' => 'pending',
    //         'payable_type' => get_class($order),
    //         'payable_id' => $order->id,
    //     ]);

    //     $params = [
    //         'apiKey'          => $this->apiKey,
    //         'commerceOrder'   => $buyOrder,
    //         'subject'         => "Orden Médica: #" . $order->id,
    //         'amount'          => (int)$order->amount,
    //         'email'           => auth()->user()->email,
    //         'urlConfirmation' => route('flow.webhook'),
    //         'urlReturn'       => route('flow.return'),
    //     ];

    //     $params['s'] = $this->makeSignature($params);

    //     try {
    //         $response = Http::asForm()->post($endpoint, $params);
    //         if ($response->successful()) {
    //             $res = $response->object();
    //             $gatewayTrx->update(['token' => $res->token]);
    //             return $res;
    //         }
    //         return null;
    //     } catch (\Exception $e) {
    //         Log::error("Error Flow Create: " . $e->getMessage());
    //         return null;
    //     }
    // }

   /**
     * Procesa el Webhook (Server-to-Server)
     */
    // public function handleWebhook(string $token)
    // {
    //     Log::info("WEBHOOK: Recibido token " . $token);
    //     $status = $this->getPaymentStatus($token);

    //     if ($status && (int)$status->status === 2) {
    //         Log::info("WEBHOOK: Pago confirmado por Flow para la orden: " . $status->commerceOrder);

    //         return DB::transaction(function () use ($status, $token) {
    //             $gatewayTrx = GatewayTransaction::where('buy_order', $status->commerceOrder)
    //                 ->where('status', 'pending')
    //                 ->first();

    //             if (!$gatewayTrx) {
    //                 Log::warning("WEBHOOK: No se encontró GatewayTransaction pendiente para " . $status->commerceOrder);
    //                 return false;
    //             }

    //             // --- ACTUALIZACIÓN CRÍTICA ---
    //             // Marcamos la transacción como autorizada para que el proceso de rechazo pueda encontrarla.
    //             $gatewayTrx->update([
    //                 'status' => 'authorized', // O 'completed' según tu convención
    //                 'flow_order_id' => $status->flowOrder, // Guardamos el 6114966
    //                 'raw_response' => json_encode($status)
    //             ]);

    //             // Cargamos orden con doctor y paciente.user para el correo del reembolso
    //             $order = MedicalOrder::with(['doctor', 'patient.user'])->find($gatewayTrx->payable_id);
    //             Log::info("WEBHOOK: Procesando Orden ID: {$order->id} | Tipo: {$order->type}");

    //             // --- FLUJO STANDARD ---
    //             if ($order->type === 'standard') {
    //                 Log::info("WEBHOOK: Entrando a flujo STANDARD");
    //                 $signatureService = app(\App\Services\SignatureService::class);
    //                 $signatureResult = $signatureService->sign($order);

    //                 if ($signatureResult && $signatureResult->success) {
    //                     Log::info("WEBHOOK: Firma exitosa, finalizando...");
    //                     $order->finalizePayment();
    //                     $this->registerTransaction($gatewayTrx, $order, $token, $status);
    //                     return true;
    //                 } else {
    //                     Log::error("WEBHOOK: Falló la firma");
    //                     $order->update(['status' => 'refund_pending']);

    //                     /**
    //                      * Invocamos reembolso pasando el flowOrder (ID de Flow)
    //                      * El gatewayTrx ahora tiene status 'authorized', así que el log de auditoría cuadrará.
    //                      */
    //                     $this->requestRefund($order, $gatewayTrx, $status->flowOrder);

    //                     return false;
    //                 }
    //             }

    //             // --- FLUJO CUSTOM ---
    //             Log::info("WEBHOOK: Entrando a flujo CUSTOM");
    //             $order->finalizePayment();
    //             $this->registerTransaction($gatewayTrx, $order, $token, $status);

    //             return true;
    //         });
    //     }

    //     Log::error("WEBHOOK: El status de Flow no fue exitoso (2). Status: " . json_encode($status));
    //     return false;
    // }
