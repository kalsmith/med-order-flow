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

    // Solo procesamos si el pago está autorizado (status 2)
    if ($status && (int)$status->status === 2) {
        return DB::transaction(function () use ($status, $token) {
            // 1. Buscar el registro de la pasarela
            $gatewayTrx = GatewayTransaction::where('buy_order', $status->commerceOrder)
                ->where('status', 'pending')
                ->first();

            if (!$gatewayTrx) return false;

            // 2. Actualizar GatewayTransaction
            $gatewayTrx->update([
                'status' => 'authorized',
                'raw_response' => (array)$status
            ]);

            // 3. Obtener la Orden Médica
            $order = $gatewayTrx->payable;
            if (!$order) return false;

            // 4. Intentar Firma Digital (Integración con el Mock Service)
            $signatureService = app(\App\Services\SignatureService::class);
            $signatureResult = $signatureService->sign($order);

            if ($signatureResult->success) {
                // --- CASO ÉXITO ---

                // Finalizamos el pago en la orden
                $order->finalizePayment();

                // Notificamos al usuario
                $signatureService->notify($order);

                // Registrar contabilidad
                Transaction::create([
                    'sender_id'      => $gatewayTrx->user_id,
                    'receiver_id'    => $order->doctor_id ?? null,
                    'reference_id'   => $order->id,
                    'reference_code' => $gatewayTrx->buy_order,
                    'amount'         => $gatewayTrx->amount,
                    'platform_fee'   => 0,
                    'type'           => 'medical_order',
                    'status'         => 'completed',
                    'metadata'       => [
                        'gateway' => 'flow',
                        'flow_token' => $token,
                        'payment_method' => $status->paymentMethod ?? 'unknown'
                    ]
                ]);

                return true;

            } else {
                // --- CASO FALLO EN FIRMA: REEMBOLSO ---

                Log::error("CRITICAL: Firma fallida para orden {$order->id}. Iniciando reembolso.");

                $order->update(['status' => 'refund_pending']);

                // Llamamos a tu método de reembolso
                $this->requestRefund($order, $gatewayTrx);

                return false;
            }
        });
    }

    return false;
}



/**
     * Procesa el reembolso en la pasarela de pago.
     */


// ... dentro de tu clase FlowService
protected function requestRefund($order, $gatewayTrx)
{
    // URL Hardcodeada para Sandbox
    $url = "https://sandbox.flow.cl/api/refund/create";

    Log::info("REEMBOLSO: Iniciando solicitud a Flow (Sandbox) para orden: " . $order->id);

    // 1. Extraer flowTrxId del JSON guardado en la transacción de forma robusta
    $raw = $gatewayTrx->raw_response;

    // Si es un string (JSON), lo decodificamos a array
    if (is_string($raw)) {
        $raw = json_decode($raw, true);
    }

    // Buscamos el ID en el JSON (Flow lo llama flowTrxId en el webhook de confirmación)
    $flowTrxId = $raw['flowTrxId'] ?? null;

    if (!$flowTrxId) {
        Log::error("REEMBOLSO: No se encontró flowTrxId. Contenido raw: " . json_encode($raw));
        return false;
    }

    // 2. Preparar parámetros
    // Nota: Usamos el email del paciente desde la relación con el usuario
    $params = [
        'apiKey'               => config('services.flow.api_key'),
        'refundCommerceOrder'  => 'REF-' . $order->id . '-' . time(),
        'receiverEmail'        => $order->user->email ?? 'cesar.labarca@example.com',
        'amount'               => (int) $gatewayTrx->amount,
        'urlCallBack'          => route('flow.refund.webhook'),
        'commerceTrxId'        => $gatewayTrx->buy_order,
        'flowTrxId'            => $flowTrxId,
    ];

    // 3. Generar la firma 's'
    $params['s'] = $this->generateSignature($params);

    // 4. Ejecutar la petición
    try {
        $response = \Illuminate\Support\Facades\Http::asForm()->post($url, $params);

        if ($response->successful()) {
            $data = $response->json();
            Log::info("REEMBOLSO: Creado exitosamente en Flow. Token: " . ($data['token'] ?? 'N/A'));

            // Actualizamos el estado de la orden
            $order->update(['status' => 'refund_pending']);
            return true;
        } else {
            Log::error("REEMBOLSO: Error en respuesta de Flow: " . $response->body());
            return false;
        }
    } catch (\Exception $e) {
        Log::error("REEMBOLSO: Excepción al conectar con Flow: " . $e->getMessage());
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
