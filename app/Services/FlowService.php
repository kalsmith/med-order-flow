<?php

namespace App\Services;

use App\Models\MedicalOrder;
use App\Models\GatewayTransaction;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
     * Crea el intento de pago en Flow y lo registra en la BD
     */
    public function createPayment(MedicalOrder $order)
    {
        $endpoint = $this->urlBase . '/payment/create';
        $buyOrder = "MED-" . strtoupper(bin2hex(random_bytes(4)));

        $gatewayTrx = GatewayTransaction::create([
            'user_id' => auth()->id(),
            'gateway' => 'flow',
            'buy_order' => $buyOrder,
            'amount' => (int)$order->amount,
            'status' => 'pending',
            'payable_type' => get_class($order),
            'payable_id' => $order->id,
        ]);

        $params = [
            'apiKey'          => $this->apiKey,
            'commerceOrder'   => $buyOrder,
            'subject'         => "Orden Médica: #" . $order->id,
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
            Log::error("Error Flow Create: " . $e->getMessage());
            return null;
        }
    }

   /**
     * Procesa el Webhook (Server-to-Server)
     */
    public function handleWebhook(string $token)
    {
        Log::info("WEBHOOK: Recibido token " . $token);
        $status = $this->getPaymentStatus($token);

        if ($status && (int)$status->status === 2) {
            Log::info("WEBHOOK: Pago confirmado por Flow para la orden: " . $status->commerceOrder);

            return DB::transaction(function () use ($status, $token) {
                $gatewayTrx = GatewayTransaction::where('buy_order', $status->commerceOrder)
                    ->where('status', 'pending')
                    ->first();

                if (!$gatewayTrx) {
                    Log::warning("WEBHOOK: No se encontró GatewayTransaction pendiente para " . $status->commerceOrder);
                    return false;
                }

                // --- ACTUALIZACIÓN CRÍTICA ---
                // Marcamos la transacción como autorizada para que el proceso de rechazo pueda encontrarla.
                $gatewayTrx->update([
                    'status' => 'authorized', // O 'completed' según tu convención
                    'flow_order_id' => $status->flowOrder, // Guardamos el 6114966
                    'raw_response' => json_encode($status)
                ]);

                // Cargamos orden con doctor y paciente.user para el correo del reembolso
                $order = MedicalOrder::with(['doctor', 'patient.user'])->find($gatewayTrx->payable_id);
                Log::info("WEBHOOK: Procesando Orden ID: {$order->id} | Tipo: {$order->type}");

                // --- FLUJO STANDARD ---
                if ($order->type === 'standard') {
                    Log::info("WEBHOOK: Entrando a flujo STANDARD");
                    $signatureService = app(\App\Services\SignatureService::class);
                    $signatureResult = $signatureService->sign($order);

                    if ($signatureResult && $signatureResult->success) {
                        Log::info("WEBHOOK: Firma exitosa, finalizando...");
                        $order->finalizePayment();
                        $this->registerTransaction($gatewayTrx, $order, $token, $status);
                        return true;
                    } else {
                        Log::error("WEBHOOK: Falló la firma");
                        $order->update(['status' => 'refund_pending']);

                        /**
                         * Invocamos reembolso pasando el flowOrder (ID de Flow)
                         * El gatewayTrx ahora tiene status 'authorized', así que el log de auditoría cuadrará.
                         */
                        $this->requestRefund($order, $gatewayTrx, $status->flowOrder);

                        return false;
                    }
                }

                // --- FLUJO CUSTOM ---
                Log::info("WEBHOOK: Entrando a flujo CUSTOM");
                $order->finalizePayment();
                $this->registerTransaction($gatewayTrx, $order, $token, $status);

                return true;
            });
        }

        Log::error("WEBHOOK: El status de Flow no fue exitoso (2). Status: " . json_encode($status));
        return false;
    }

    /**
     * Registra la transacción contable interna
     */
    private function registerTransaction($gatewayTrx, $order, $token, $status) {
        $receiverId = $order->doctor ? $order->doctor->user_id : null;

        if (!$receiverId) {
            Log::error("No se pudo registrar transacción: El doctor asignado a la orden {$order->id} no tiene un user_id vinculado.");
        }

        Transaction::create([
            'sender_id'      => $gatewayTrx->user_id,
            'receiver_id'    => $receiverId,
            'reference_id'   => $order->id,
            'reference_code' => $gatewayTrx->buy_order,
            'amount'         => $gatewayTrx->amount,
            'type'           => 'medical_order',
            'status'         => 'completed',
            'metadata'       => [
                'gateway' => 'flow',
                'flow_token' => $token,
                'payment_method' => $status->paymentMethod ?? 'unknown'
            ]
        ]);
    }

    /**
     * Obtiene el estado del pago desde Flow
     */
    public function getPaymentStatus(string $token)
    {
        $params = ['apiKey' => $this->apiKey, 'token' => $token];
        $params['s'] = $this->makeSignature($params);
        $response = Http::get($this->urlBase . '/payment/getStatus', $params);
        return $response->successful() ? $response->object() : null;
    }

    /**
     * Genera la firma HMAC para Flow
     */
    protected function makeSignature(array $params): string
    {
        ksort($params);
        $toSign = "";
        foreach ($params as $key => $value) {
            $toSign .= $key . $value;
        }
        return hash_hmac('sha256', $toSign, $this->secretKey);
    }

    /**
     * Solicita un reembolso a Flow
     */
public function requestRefund(MedicalOrder $order, GatewayTransaction $gatewayTrx, $flowTrxId = null)
{
    $endpoint = 'https://sandbox.flow.cl/api/refund/create';
    $refundOrder = "REF-" . strtoupper(bin2hex(random_bytes(4)));

    // 1. Preparamos parámetros básicos
    $params = [
        'apiKey'               => $this->apiKey,
        'refundCommerceOrder'  => $refundOrder,
        'receiverEmail'        => $order->patient->user->email,
        'amount'               => (int)$order->amount,
        'urlCallBack'          => route('flow.refund.webhook'),
    ];

    // 2. Lógica de identificación (Aquí es donde queremos ver qué prefiere Flow)
    if ($flowTrxId && is_numeric($flowTrxId)) {
        $params['flowTrxId'] = $flowTrxId;
        Log::info("--- [DEBUG REFUND] Usando ID Numérico (flowTrxId) ---", ['val' => $flowTrxId]);
    } else {
        $params['commerceTrxId'] = $gatewayTrx->buy_order;
        Log::info("--- [DEBUG REFUND] Usando ID Alfanumérico (commerceTrxId) ---", ['val' => $gatewayTrx->buy_order]);
    }

    // 3. Generamos firma y logeamos parámetros finales (sin keys sensibles)
    $params['s'] = $this->makeSignature($params);

    Log::info("--- [DEBUG REFUND] PARÁMETROS ENVIADOS A FLOW ---", [
        'endpoint' => $endpoint,
        'params_sent' => array_diff_key($params, ['apiKey' => '', 's' => ''])
    ]);

    try {
        $response = Http::asForm()->post($endpoint, $params);

        // 4. LOG AGRESIVO DE RESPUESTA
        Log::info("--- [DEBUG REFUND] RESPUESTA API FLOW ---", [
            'http_status' => $response->status(),
            'body' => $response->json(),
        ]);

        if ($response->successful()) {
            $res = $response->object();

            $order->update([
                'flow_refund_id' => $res->token,
                'status'         => 'refund_pending',
                'internal_notes' => $order->internal_notes . "\n[Reembolso Creado en Flow: {$res->flowRefundOrder} el " . now() . "]"
            ]);

            Log::info("--- [DEBUG REFUND] REEMBOLSO EXITOSO ---", ['token' => $res->token]);
            return true;
        }

        Log::error("--- [DEBUG REFUND] API RECHAZÓ LA SOLICITUD ---", [
            'error_body' => $response->json()
        ]);

        return false;

    } catch (\Exception $e) {
        Log::error("--- [DEBUG REFUND] EXCEPCIÓN CRÍTICA ---", [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return false;
    }
}

}
