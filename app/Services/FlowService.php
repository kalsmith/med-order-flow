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
        'urlConfirmation' => route('flow.confirmation'), // Coincide con FlowController
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
                // 1. Buscar el registro de la pasarela
                $gatewayTrx = GatewayTransaction::where('buy_order', $status->commerceOrder)
                    ->where('status', 'pending')
                    ->first();

                if (!$gatewayTrx) return false;

                // 2. Actualizar GatewayTransaction (Pasarela)
                $gatewayTrx->update([
                    'status' => 'authorized',
                    'raw_response' => (array)$status
                ]);

                // 3. Obtener la Orden Médica
                $order = $gatewayTrx->payable;
                if ($order) {
                    $order->update(['status' => 'paid']);

                    // 4. CREAR EL MOVIMIENTO EN TRANSACTION (Contabilidad)
                    // Aquí es donde vive el dinero real en tu sistema
                    Transaction::create([
                        'sender_id'    => $gatewayTrx->user_id, // El paciente que pagó
                        'receiver_id'  => $order->doctor_id ?? null, // Doctor asignado o null
                        'reference_id' => $order->id, // UUID de la Orden Médica
                        'reference_code' => $gatewayTrx->buy_order, // Vinculamos con la pasarela
                        'amount'       => $gatewayTrx->amount,
                        'platform_fee' => 0, // Ajustar según tu lógica de comisión
                        'type'         => 'medical_order',
                        'status'       => 'completed',
                        'metadata'     => [
                            'gateway' => 'flow',
                            'flow_token' => $token,
                            'payment_method' => $status->paymentMethod ?? 'unknown'
                        ]
                    ]);
                }

                return true;
            });
        }
        return false;
    }

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
