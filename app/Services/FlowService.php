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
    Log::info("=== [WEBHOOK FLOW START] ===");
    Log::info("TOKEN RECIBIDO: " . $token);

    try {
        // 1. Obtener datos de Flow
        $statusResponse = $this->getPaymentStatus($token);
        $paymentData = (array) $statusResponse;

        Log::info("DATOS FLOW (Decoded):", [
            'commerceOrder' => $paymentData['commerceOrder'] ?? 'MISSING',
            'status' => $paymentData['status'] ?? 'UNKNOWN',
            'amount' => $paymentData['amount'] ?? '0'
        ]);

        // 2. BÚSQUEDA DE LA TRANSACCIÓN TÉCNICA Y LA ORDEN COMERCIAL
        $gatewayTrx = GatewayTransaction::where('buy_order', $paymentData['commerceOrder'] ?? null)
            ->orWhere('token', $token)
            ->first();

        if (!$gatewayTrx) {
            Log::error("CRITICAL: No existe GatewayTransaction para token: " . $token);
            throw new \Exception("Transacción de pasarela no encontrada.");
        }

        $order = Order::find($gatewayTrx->payable_id);

        if (!$order) {
            Log::error("CRITICAL: La orden UUID " . $gatewayTrx->payable_id . " no existe.");
            throw new \Exception("Orden comercial no encontrada.");
        }

        /**
         * 3. RESCATE DE DATOS (ESTRATEGIA FALLBACK)
         * Prioridad 1: Tabla 'orders' (Actualizada en CheckoutController)
         * Prioridad 2: Metadata local de la transacción (si existiera)
         * Prioridad 3: Respuesta de Flow (que suele venir vacía)
         */
        $examTypeId = $order->exam_type_id ?? ($gatewayTrx->metadata['exam_type_id'] ?? ($paymentData['optional']['exam_type_id'] ?? null));
        $orderType  = $order->type ?? ($gatewayTrx->metadata['type'] ?? ($paymentData['optional']['type'] ?? 'standard'));

        Log::info("METADATA RECUPERADA:", [
            'exam_id' => $examTypeId,
            'type' => $orderType,
            'source' => $order->exam_type_id ? 'order_table' : 'metadata_fallback'
        ]);

        // 4. TRANSACCIÓN ATÓMICA DE BASE DE DATOS
        return DB::transaction(function () use ($order, $gatewayTrx, $paymentData, $token, $examTypeId, $orderType) {

            Log::info("PROCESANDO PAGO: Actualizando registros y contabilidad.");

            // A. Actualizar estado técnico (Pasarela)
            $gatewayTrx->update([
                'status' => 'authorized',
                'raw_response' => json_encode($paymentData)
            ]);

            // B. Registro en tabla Transaction (Contabilidad Universal)
            $this->registerTransaction($gatewayTrx, $order, $token, (object)$paymentData);

            // C. Lógica de Negocio según el Tipo de Orden
            if ($orderType === 'standard' || $orderType === 'medical_purchase') {

                if (!$examTypeId) {
                    throw new \Exception("Falta exam_type_id para procesar la receta médica.");
                }

                $exam = ExamType::findOrFail($examTypeId);

                // Rotación de médicos
                $doctor = Doctor::getNextAvailableForSpecialty($exam->specialty_id);

                if (!$doctor) {
                    Log::error("ERROR ROTACIÓN: No hay médicos para especialidad " . $exam->specialty_id);
                    throw new \Exception("No hay médicos disponibles.");
                }

                // Creación de la Receta (Prescription)
                $prescription = Prescription::create([
                    'id'                => (string) \Illuminate\Support\Str::uuid(),
                    'order_id'          => $order->id,
                    'doctor_id'         => $doctor->id,
                    'exam_type_id'      => $exam->id,
                    'status'            => 'active',
                    'verification_code' => strtoupper(\Illuminate\Support\Str::random(8)),
                ]);

                $doctor->update(['last_assigned_at' => now()]);

                // Firma Digital
                try {
                    $signatureService = app(\App\Services\SignatureService::class);
                    $signatureResult = $signatureService->sign($prescription);

                    if ($signatureResult && $signatureResult->success) {
                        $order->update(['status' => 'paid']);
                        Log::info("=== WEBHOOK FINALIZADO: RECETA FIRMADA ===");
                    } else {
                        throw new \Exception("Fallo en SignatureService.");
                    }
                } catch (\Exception $e) {
                    Log::error("FALLO EN FIRMA: " . $e->getMessage());
                    $order->update(['status' => 'failed']);
                    throw $e;
                }

            } else {
                // Otros flujos (Suscripciones, etc.)
                Log::info("FLUJO ALTERNATIVO: Marcando orden como pagada.");
                $order->update(['status' => 'paid']);
            }

            return true;
        });

    } catch (\Exception $e) {
        Log::error("=== [WEBHOOK FLOW FATAL ERROR] ===");
        Log::error("MENSAJE: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Registra el movimiento contable interno en la tabla Transaction.
 * Esta tabla centraliza todos los ingresos del sitio.
 */
private function registerTransaction($gatewayTrx, $order, $token, $status)
{
    return \App\Models\Transaction::create([
        'sender_id'      => $gatewayTrx->user_id,
        'receiver_id'    => null,
        'reference_id'   => $order->id, // UUID de la Order
        'reference_code' => $gatewayTrx->buy_order,
        'amount'         => $gatewayTrx->amount,
        'type'           => $order->type ?? 'medical_purchase',
        'status'         => 'completed',
        'metadata'       => [
            'gateway'        => 'flow',
            'flow_token'     => $token,
            'payment_method' => $status->paymentData['method'] ?? 'unknown',
            'exam_type_id'   => $order->exam_type_id
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
