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

            // --- 3. NORMALIZACIÓN DE FLUJO EN CALIENTE ---
            // Si tiene un ID de examen, debe ser STANDARD sin importar el string en 'type'
            $effectiveType = $order->type;
            if (!empty($order->exam_type_id)) {
                $effectiveType = 'standard';
            }

            // 4. Lógica de Negocio según el tipo efectivo
            if ($effectiveType === 'standard' || $effectiveType === 'medical_purchase') {
                // FLUJO ESTÁNDAR: Firma automática (Laura Martínez)
                $processStatus = $this->processMedicalOrder($order);
                $this->handleProcessFailure($processStatus, $order, $statusResponse);

            } elseif ($effectiveType === 'multiple') {
                // FLUJO MÚLTIPLE: Firma automática usando texto libre
                $processStatus = $this->processMedicalOrder($order, $order->custom_description);
                $this->handleProcessFailure($processStatus, $order, $statusResponse);

            } else {
                // FLUJO CUSTOM: Requiere revisión humana (Texto libre sin examen previo)
                $this->createPendingPrescription($order);
                $order->update([
                    'status' => 'paid',
                    'flow_order_id' => $statusResponse->flowOrder
                ]);
                Log::info("WEBHOOK: Orden personalizada preparada para revisión médica.", ['order_id' => $order->id]);
            }

            Log::info("=== [FLOW WEBHOOK SUCCESS] ===", [
                'order_id' => $order->id,
                'processed_as' => $effectiveType
            ]);
            return true;
        });

    } catch (\Exception $e) {
        Log::error("ERROR FATAL WEBHOOK: " . $e->getMessage());
        throw $e;
    }
}


    /**
     * Procesa el fallo de los flujos automáticos (Standard/Multiple)
     */
    private function handleProcessFailure($status, Order $order, $statusResponse)
    {
        if (!$status) {
            Log::error("WEBHOOK: Proceso médico automático falló. Iniciando reembolso.");

            $refundService = app(RefundService::class);
            $refundResult = $refundService->createRefund($order, $statusResponse->flowOrder);

            if (!$refundResult) {
                $order->update([
                    'status' => 'manual_review',
                    'flow_order_id' => $statusResponse->flowOrder
                ]);
            }
        }
    }

    private function createPendingPrescription(Order $order)
    {
        return Prescription::create([
            'id'                => (string) Str::uuid(),
            'order_id'          => $order->id,
            'doctor_id'         => null,
            'exam_type_id'      => $order->exam_type_id,
            'type'              => 'custom',
            'status'            => 'active',
            'clinical_context'  => $order->clinical_context,
            'custom_description'=> $order->custom_description,
        ]);
    }

    private function processMedicalOrder(Order $order, $customContent = null)
{
    Log::info(" [DEBUG-FLOW] === INICIO PROCESO MÉDICO === ", [
        'order_id' => $order->id,
        'type' => $order->type
    ]);

    try {
        $specialtyId = null;

        // 1. DETERMINAR ESPECIALIDAD DINÁMICAMENTE
        if ($order->exam_type_id) {
            // Caso Standard/Pack: La especialidad viene del examen base
            $exam = ExamType::find($order->exam_type_id);
            $specialtyId = $exam ? $exam->specialty_id : null;
            Log::info(" [DEBUG-FLOW] Especialidad desde exam_type_id: " . $specialtyId);
        }

        if (!$specialtyId && $order->type === 'multiple') {
            // Caso "Arma tu pack": Buscamos la especialidad de los exámenes que componen el pack
            // Extraemos los IDs desde la descripción o podrías pasarlos en el request
            // Aquí lo más seguro es mirar los exámenes que el usuario eligió.

            // Si no tenemos los IDs a mano, buscamos el primer médico de Medicina General (ID 12)
            // Pero intentemos ser precisos:
            Log::info(" [DEBUG-FLOW] Flujo múltiple: Intentando inferir especialidad.");

            // Si todos tus exámenes de pack son Medicina General (12), lo buscamos así:
            $specialtyId = 12;
        }

        // Si sigue siendo null, usamos 12 (Medicina General) como fallback de negocio, no hardcodeado
        $specialtyId = $specialtyId ?? 12;

        // 2. BUSCAR MÉDICO (Rotación)
        Log::info(" [DEBUG-FLOW] Buscando médico para especialidad: " . $specialtyId);
        $doctor = Doctor::getNextAvailableForSpecialty($specialtyId);

        if (!$doctor) {
            // LOG AGRESIVO SI FALLA EL MODELO DOCTOR
            Log::error(" [DEBUG-FLOW] getNextAvailableForSpecialty({$specialtyId}) devolvió NULL.");

            // Verificamos si hay médicos en esa especialidad para descartar error de lógica vs falta de datos
            $count = Doctor::where('specialty_id', $specialtyId)->where('is_active', true)->count();
            Log::info(" [DEBUG-FLOW] Conteo de médicos activos para especialidad {$specialtyId}: {$count}");

            throw new \Exception("No hay médicos disponibles para la especialidad {$specialtyId}.");
        }

        Log::info(" [DEBUG-FLOW] Médico seleccionado: {$doctor->name} (ID: {$doctor->id})");

        // 3. CREAR PRESCRIPCIÓN
        $prescription = Prescription::create([
            'id' => (string) Str::uuid(),
            'order_id' => $order->id,
            'doctor_id' => $doctor->id,
            'exam_type_id' => $order->exam_type_id,
            'status' => 'active',
            'verification_code' => strtoupper(Str::random(8)),
            'clinical_context' => $customContent ?? $order->clinical_context,
        ]);

        // 4. FIRMA
        Log::info(" [DEBUG-FLOW] Enviando a firma electrónica...");
        $signatureService = app(SignatureService::class);
        $signatureResult = $signatureService->sign($prescription);

        if ($signatureResult && $signatureResult->success) {
            $prescription->update(['status' => 'signed', 'signed_at' => now()]);
            $order->update(['status' => 'paid']);
            $doctor->update(['last_assigned_at' => now()]);
            Log::info(" [DEBUG-FLOW] PROCESO COMPLETADO EXITOSAMENTE.");
            return true;
        }

        throw new \Exception("La firma electrónica falló o no retornó éxito.");

    } catch (\Exception $e) {
        Log::error(" [DEBUG-FLOW] EXCEPCIÓN: " . $e->getMessage());
        $order->update(['status' => 'manual_review']);
        return false;
    }
}




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
            'flowTrxId'             => $flowTrxId ?? $gatewayTrx->flow_order_id,
        ];

        $params['s'] = $this->makeSignature($params);

        try {
            Log::info("Enviando solicitud de reembolso a Flow...");
            $response = Http::asForm()->post($endpoint, $params);

            if ($response->successful()) {
                $res = $response->object();
                $order->update([
                    'flow_refund_id' => $res->token,
                    'status'         => 'refund_pending'
                ]);
                Log::info("Reembolso solicitado con éxito: " . $res->token);
                return true;
            }

            Log::error("Flow Refund API rechazó la solicitud: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("Excepción en requestRefund: " . $e->getMessage());
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
