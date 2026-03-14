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

    public function createPayment(Order $order)
    {
        $endpoint = $this->urlBase . '/payment/create';
        $buyOrder = "ORD-" . substr($order->id, 0, 8) . "-" . strtoupper(bin2hex(random_bytes(2)));

        $gatewayTrx = GatewayTransaction::firstOrCreate(
            ['payable_type' => get_class($order), 'payable_id' => $order->id, 'status' => 'pending'],
            [
                'user_id' => auth()->id(),
                'gateway' => 'flow',
                'buy_order' => $buyOrder,
                'amount' => (int)$order->amount,
                'raw_response' => ['exam_type_id' => $order->exam_type_id, 'type' => $order->type]
            ]
        );

        $params = [
            'apiKey'          => $this->apiKey,
            'commerceOrder'   => $gatewayTrx->buy_order,
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
            return null;
        } catch (\Exception $e) {
            Log::error("Excepción en Flow createPayment: " . $e->getMessage());
            return null;
        }
    }

    public function handleWebhook($token)
    {
        Log::info("=== [FLOW WEBHOOK START] ===", ['token' => $token]);

        try {
            $statusResponse = $this->getPaymentStatus($token);

            if (!$statusResponse || (int)$statusResponse->status !== 2) {
                Log::warning("Pago no autorizado en Flow", (array)$statusResponse);
                return false;
            }

            $gatewayTrx = GatewayTransaction::where('token', $token)->first();
            if (!$gatewayTrx) throw new \Exception("Transacción técnica no encontrada.");

            $order = Order::with('patient.user')->findOrFail($gatewayTrx->payable_id);

            return DB::transaction(function () use ($order, $gatewayTrx, $statusResponse, $token) {
                // 1. Actualizar pasarela técnica
                $gatewayTrx->update([
                    'status' => 'authorized',
                    'flow_order_id' => $statusResponse->flowOrder,
                    'raw_response' => json_encode($statusResponse)
                ]);

                // 2. Registro Contable
                $this->registerTransaction($gatewayTrx, $order, $token, $statusResponse);

                // 3. Lógica de Negocio
                if ($order->type === 'standard' || $order->type === 'medical_purchase') {
                    $processStatus = $this->processMedicalOrder($order);

                    // Si el proceso de firma falla críticamente y decides que prefieres reembolsar
                    // en lugar de dejar en revisión manual, llamarías a requestRefund aquí.
                    if (!$processStatus) {
                        Log::error("WEBHOOK: Proceso médico falló. La orden queda en revisión/reembolso.");
                        // Opcional: $this->requestRefund($order, $gatewayTrx, $statusResponse->flowOrder);
                    }
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

    private function processMedicalOrder(Order $order)
    {
        try {
            $exam = ExamType::findOrFail($order->exam_type_id);
            $doctor = Doctor::getNextAvailableForSpecialty($exam->specialty_id);

            if (!$doctor) throw new \Exception("No hay médicos disponibles.");

            $prescription = Prescription::create([
                'id' => (string) Str::uuid(),
                'order_id' => $order->id,
                'doctor_id' => $doctor->id,
                'exam_type_id' => $exam->id,
                'status' => 'active',
                'verification_code' => strtoupper(Str::random(8)),
            ]);

            $doctor->update(['last_assigned_at' => now()]);

            $signatureService = app(SignatureService::class);
            $signatureResult = $signatureService->sign($prescription);

            if ($signatureResult && $signatureResult->success) {
                $prescription->update(['status' => 'signed', 'signed_at' => now()]);
                $order->update(['status' => 'paid']);
                return true;
            }

            throw new \Exception("Firma del servicio no exitosa.");

        } catch (\Exception $e) {
            Log::error("Error en processMedicalOrder: " . $e->getMessage());
            $order->update(['status' => 'manual_review']);
            return false; // Retornamos false para que el webhook sepa que hubo un problema
        }
    }

    /**
     * Registro de Reembolso (Mismo que tenías antes, adaptado)
     */
    public function requestRefund(Order $order, GatewayTransaction $gatewayTrx, $flowTrxId = null)
    {
        $endpoint = $this->urlBase . '/refund/create';
        $refundOrder = "REF-" . strtoupper(bin2hex(random_bytes(4)));

        $params = [
            'apiKey'                => $this->apiKey,
            'refundCommerceOrder'   => $refundOrder,
            'receiverEmail'         => $order->patient->user->email,
            'amount'                => (int)$order->amount,
            'urlCallBack'           => route('flow.refund.webhook'),
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
                $order->update(['status' => 'refund_pending']);
                Log::info("Reembolso solicitado con éxito para Orden: " . $order->id);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error("Error solicitando reembolso: " . $e->getMessage());
            return false;
        }
    }

    private function registerTransaction($gatewayTrx, $order, $token, $statusResponse)
    {
        return Transaction::create([
            'sender_id'      => $gatewayTrx->user_id,
            'reference_id'   => $order->id,
            'reference_code' => $gatewayTrx->buy_order,
            'amount'         => $gatewayTrx->amount,
            'type'           => $order->type ?? 'standard',
            'status'         => 'completed',
            'metadata'       => [
                'gateway' => 'flow',
                'flow_token' => $token,
                'payment_method' => $statusResponse->paymentData->media ?? 'unknown',
            ]
        ]);
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
