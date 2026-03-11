<?php

namespace App\Services;

use App\Models\MedicalOrder;
use App\Models\GatewayTransaction;
use App\Models\Transaction; // Importamos el modelo de movimientos
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
     * Crea el pago y registra el intento en GatewayTransaction
     */

    // ... imports igual que antes ...

public function createPayment(MedicalOrder $order)
{
    $endpoint = $this->urlBase . '/payment/create';
    $buyOrder = "MED-" . strtoupper(bin2hex(random_bytes(4)));

    // 1. Registro técnico (Único lugar donde se crea)
    $gatewayTrx = GatewayTransaction::create([
        'user_id' => auth()->id(),
        'gateway' => 'flow',
        'buy_order' => $buyOrder,
        'amount' => (int)$order->amount,
        'status' => 'pending',
        'payable_type' => get_class($order),
        'payable_id' => $order->id,
    ]);

    // 2. Parámetros para Flow (Coherentes con las rutas de tu Controller)
    $params = [
        'apiKey'          => $this->apiKey,
        'commerceOrder'   => $buyOrder,
        'subject'         => "Orden Médica: #" . $order->id,
        'amount'          => (int)$order->amount,
        'email'           => auth()->user()->email,
        'urlConfirmation' => route('flow.webhook'), // <-- AQUÍ DEBE DECIR flow.webhook
        'urlReturn'       => route('flow.return'),        // Coincide con FlowController
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
        return null;
    }
}


    /**
     * Procesa el Webhook, cierra la pasarela y genera el movimiento contable
     */



    public function handleWebhook(string $token)
    {
        $status = $this->getStatus($token);

        if ($status && (int)$status->status === 2) {
            return DB::transaction(function () use ($status, $token) {
                $gatewayTrx = GatewayTransaction::where('buy_order', $status->commerceOrder)
                    ->where('status', 'pending')
                    ->first();

                if (!$gatewayTrx) return false;

                $gatewayTrx->update([
                    'status' => 'authorized',
                    'raw_response' => (array)$status
                ]);

                $order = $gatewayTrx->payable;
                if (!$order) return false;

                // --- DIFERENCIACIÓN DE FLUJO ---

                if ($order->type === 'custom') {
                    /**
                     * FLUJO CUSTOM:
                     * Solo marcamos como pagada. NO FIRMAMOS.
                     * El médico la verá en su bandeja como 'paid'.
                     */
                    $order->finalizePayment(); // Esto la dejará en 'paid' según tu modelo

                    // Registrar contabilidad (el dinero ya entró)
                    $this->registerTransaction($gatewayTrx, $order, $token, $status);

                    Log::info("Orden CUSTOM {$order->id} pagada. Esperando revisión médica.");
                    return true;
                }

                /**
                 * FLUJO STANDARD:
                 * Intento de firma automática (como lo tienes ahora).
                 */
                $signatureService = app(\App\Services\SignatureService::class);
                $signatureResult = $signatureService->sign($order);

                if ($signatureResult->success) {
                    $order->finalizePayment(); // Esto la dejará en 'signed'
                    $signatureService->notify($order);
                    $this->registerTransaction($gatewayTrx, $order, $token, $status);
                    return true;
                } else {
                    Log::error("Falla firma automática en orden STANDARD {$order->id}. Reembolsando.");
                    $order->update(['status' => 'refund_pending']);
                    $this->requestRefund($order, $gatewayTrx);
                    return false;
                }
            });
        }
        return false;
    }

    // Extraje esto a un método para no repetir código
    private function registerTransaction($gatewayTrx, $order, $token, $status) {
        Transaction::create([
            'sender_id'      => $gatewayTrx->user_id,
            'receiver_id'    => $order->doctor_id ?? null,
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
     * Procesa el reembolso en la pasarela de pago.
     */


// ... dentro de tu clase FlowService
protected function requestRefund($order, $gatewayTrx)
{
    $url = "https://sandbox.flow.cl/api/refund/create";
    Log::info("REEMBOLSO: Iniciando solicitud a Flow (Sandbox) para orden: " . $order->id);

    // 1. Extraer el ID de transacción de forma robusta
    $raw = $gatewayTrx->raw_response;
    if (is_string($raw)) {
        $raw = json_decode($raw, true);
    }

    /**
     * IMPORTANTE: Flow devuelve 'flowTrxId' en el webhook de confirmación.
     * Si no está en el JSON, probamos con 'transaction_id' de la tabla,
     * o buscamos llaves alternativas que Flow usa en Sandbox.
     */
    $flowTrxId = $raw['flowTrxId']
                 ?? $raw['flowOrder']
                 ?? $gatewayTrx->transaction_id
                 ?? null;

    if (!$flowTrxId) {
        Log::error("REEMBOLSO: No se encontró flowTrxId. Datos disponibles: " . json_encode([
            'transaction_id_col' => $gatewayTrx->transaction_id,
            'raw_keys' => array_keys($raw ?? [])
        ]));
        return false;
    }

    // 2. Preparar parámetros
    $params = [
        'apiKey'               => config('services.flow.api_key'),
        'refundCommerceOrder'  => 'REF-' . $order->id . '-' . time(),
        'receiverEmail'        => $order->patient->user->email ?? auth()->user()->email,
        'amount'               => (int) $gatewayTrx->amount,
        'urlCallBack'          => route('flow.refund.webhook'),
        'commerceTrxId'        => $gatewayTrx->buy_order,
        'flowTrxId'            => $flowTrxId,
    ];

    // 3. Firma y Envío (Igual que antes...)
    $params['s'] = $this->generateSignature($params);

    try {
        $response = Http::asForm()->post($url, $params);

        if ($response->successful()) {
            $data = $response->json();
            Log::info("REEMBOLSO: Solicitud aceptada por Flow. Token: " . ($data['token'] ?? 'N/A'));
            $order->update(['status' => 'refund_pending']);
            return true;
        } else {
            Log::error("REEMBOLSO: Flow rechazó la petición: " . $response->body());
            return false;
        }
    } catch (\Exception $e) {
        Log::error("REEMBOLSO: Error de conexión: " . $e->getMessage());
        return false;
    }
}

/**
 * Método para generar la firma requerida por Flow
 */
protected function generateSignature(array $params)
{
    $secret = config('services.flow.secret_key');

    // 1. Ordenar por llave alfabéticamente (Requisito de Flow)
    ksort($params);

    // 2. Concatenar llave + valor
    $toSign = "";
    foreach ($params as $key => $value) {
        $toSign .= $key . $value;
    }

    // 3. HMAC SHA256
    return hash_hmac('sha256', $toSign, $secret);
}


public function getRefundStatus($token)
{
    $url = "https://sandbox.flow.cl/api/refund/getStatus";
    $params = [
        'apiKey' => config('services.flow.api_key'),
        'token'  => $token
    ];
    $params['s'] = $this->generateSignature($params);

    $response = Http::get($url, $params);
    return $response->json();
}




/**
 * Genera la firma HMAC-SHA256 (Estándar de Flow)
 */





    public function getStatus(string $token)
    {
        $params = [
            'apiKey' => $this->apiKey,
            'token'  => $token
        ];
        $params['s'] = $this->makeSignature($params);

        try {
            $response = Http::get($this->urlBase . '/payment/getStatus', $params);
            return $response->successful() ? $response->object() : null;
        } catch (\Exception $e) {
            Log::error("Flow GetStatus Exception: " . $e->getMessage());
            return null;
        }
    }
}
