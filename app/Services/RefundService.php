<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefundService
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
     * Crea un reembolso en Flow y actualiza la orden.
     */
    public function createRefund(Order $order, $flowTrxId)
    {
        $endpoint = $this->urlBase . '/refund/create';
        // Generamos un identificador único para el reembolso
        $refundOrder = "REF-" . strtoupper(substr($order->id, 0, 4)) . "-" . strtoupper(bin2hex(random_bytes(2)));

        $params = [
            'apiKey'              => $this->apiKey,
            'refundCommerceOrder' => $refundOrder,
            'receiverEmail'       => $order->patient->user->email,
            'amount'              => (int) $order->amount,
            'urlCallBack'         => route('flow.refund.webhook'),
            'flowTrxId'           => $flowTrxId,
        ];

        $params['s'] = $this->makeSignature($params);

        try {
            Log::info("Solicitando reembolso a Flow para Orden: {$order->id}");
            $response = Http::asForm()->post($endpoint, $params);

            if ($response->successful()) {
                $res = $response->object();

                // Importante: Cambiamos el estado a refund_pending para avisar al front
                $order->update([
                    'status' => 'refund_pending',
                    'flow_refund_id' => $res->token
                ]);

                Log::info("Reembolso aceptado por Flow", ['token' => $res->token]);
                return $res;
            }

            Log::error("Flow Refund API Error: " . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error("Excepción en RefundService: " . $e->getMessage());
            return null;
        }
    }

    private function makeSignature(array $params)
    {
        ksort($params);
        $toSign = "";
        foreach ($params as $key => $value) {
            $toSign .= $key . $value;
        }
        return hash_hmac('sha256', $toSign, $this->secretKey);
    }
}
