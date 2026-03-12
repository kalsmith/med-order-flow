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

        // Registro de la transacción técnica
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

            $order = MedicalOrder::with('doctor')->find($gatewayTrx->payable_id);
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


    private function registerTransaction($gatewayTrx, $order, $token, $status) {
        // Obtenemos el ID de usuario del doctor, no el ID de la tabla doctors
        $receiverId = $order->doctor ? $order->doctor->user_id : null;

        if (!$receiverId) {
            Log::error("No se pudo registrar transacción: El doctor asignado a la orden {$order->id} no tiene un user_id vinculado.");
            // Opcional: podrías lanzar una excepción aquí para hacer rollback del webhook
        }

        Transaction::create([
            'sender_id'      => $gatewayTrx->user_id,
            'receiver_id'    => $receiverId, // Corregido: Ahora usa el ID de la tabla users
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


    /**
 * Solicita un reembolso a Flow si la firma falla
 */
public function requestRefund(MedicalOrder $order, GatewayTransaction $gatewayTrx)
{
    $endpoint = $this->urlBase . '/refund/create';

    // Generamos un ID de reembolso único para nuestra BD
    $refundId = "REF-" . strtoupper(bin2hex(random_bytes(4)));

    $params = [
        'apiKey'         => $this->apiKey,
        'amount'         => (int)$order->amount,
        'commerceTrxId'  => $gatewayTrx->buy_order, // La orden original de Flow
        'externalRefundId' => $refundId,
        'receiverEmail'  => $order->patient->user->email,
        'urlCallBack'    => route('flow.refund.webhook'), // Ruta para confirmar el reembolso
    ];

    $params['s'] = $this->makeSignature($params);

    try {
        Log::info("SOLICITANDO REEMBOLSO: Orden {$order->id} por fallo en firma.");

        $response = Http::asForm()->post($endpoint, $params);

        if ($response->successful()) {
            $res = $response->object();
            // Guardamos el ID de reembolso de Flow en la orden
            $order->update([
                'flow_refund_id' => $res->token, // O el ID que retorne Flow
                'internal_notes' => ($order->internal_notes . "\n[Reembolso solicitado el " . now() . "]")
            ]);

            Log::info("REEMBOLSO TRAMITADO: Token de Flow: " . $res->token);
            return true;
        }

        Log::info("ERROR API FLOW REEMBOLSO: " . $response->body());
        return false;

    } catch (\Exception $e) {
        Log::info("EXCEPCIÓN EN REEMBOLSO: " . $e->getMessage());
        return false;
    }
}


}
