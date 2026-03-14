<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\FlowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    /**
     * Inicia el proceso de pago redirigiendo a Flow usando el nuevo modelo de Orden Comercial.
        */
    public function process(Order $order, FlowService $flowService)
    {
        // 1. CARGA DE RELACIÓN: Aseguramos que el paciente esté disponible
        $order->loadMissing('patient');

        // 2. SEGURIDAD: Validación de propiedad de la orden
        $patientUserId = (string) ($order->patient->user_id ?? '');
        $authId = (string) auth()->id();

        if ($patientUserId !== $authId) {
            Log::warning("Intento de pago no autorizado", [
                'order_id' => $order->id,
                'owner_id' => $patientUserId,
                'attempt_by' => $authId
            ]);
            abort(403, 'No tienes permiso para pagar esta orden.');
        }

        // 3. ESTADO: Evitar procesar pagos ya realizados
        if ($order->status !== 'pending') {
            return redirect()->route('patient.orders')
                ->with('info', 'Esta orden ya se encuentra pagada o en proceso.');
        }

        try {
            /**
             * 4. PREPARACIÓN DE METADATA:
             * Capturamos los datos del examen que vienen del PatientOrderController@store
             * para que viajen a la pasarela y vuelvan en el Webhook.
             */
            $extraData = [
                'exam_type_id' => request('exam_type_id'),
                'type'         => request('type', 'standard'), // default a standard si no viene
            ];

            /**
             * 5. PASARELA: Llamada al servicio de Flow.
             */
            $response = $flowService->createPayment($order, $extraData);

            if ($response && isset($response->token)) {
                // Redirección externa a la pasarela de Flow
                return redirect()->away($response->url . "?token=" . $response->token);
            }

            throw new \Exception('La respuesta de la pasarela no contiene un token de pago válido.');

        } catch (\Exception $e) {
            Log::error("Error crítico en Checkout@process: " . $e->getMessage(), [
                'order_id' => $order->id,
                'extra_data' => $extraData ?? null
            ]);

            return redirect()->route('patient.orders')
                ->with('error', 'No pudimos conectar con la pasarela de pagos. Por favor, intenta más tarde.');
        }
    }
}


    /**
     * Inicia el proceso de pago redirigiendo a Flow
     */
    // public function process(MedicalOrder $order, FlowService $flowService)
    // {
    //     // 1. CARGA DE RELACIÓN: Aseguramos que patient esté cargado
    //     $order->loadMissing('patient');

    //     // 2. SEGURIDAD: Validación estricta con casting a String
    //     // Comparamos como string por si uno es UUID y el otro Integer
    //     $patientUserId = (string) ($order->patient->user_id ?? '');
    //     $authId = (string) auth()->id();

    //     if ($patientUserId !== $authId) {
    //         Log::warning("Intento de pago no autorizado", [
    //             'order_id' => $order->id,
    //             'owner_id' => $patientUserId,
    //             'attempt_by' => $authId
    //         ]);
    //         abort(403, 'No tienes permiso para pagar esta orden.');
    //     }

    //     // 3. ESTADO: Evitar doble pago
    //     if ($order->status !== 'pending') {
    //         return redirect()->route('patient.orders')
    //             ->with('info', 'Esta orden ya se encuentra pagada o en proceso.');
    //     }

    //     try {
    //         // 4. PASARELA: Llamada al servicio de Flow
    //         $response = $flowService->createPayment($order);

    //         if ($response && isset($response->token)) {
    //             // Redirección externa a Flow
    //             return redirect()->away($response->url . "?token=" . $response->token);
    //         }

    //         throw new \Exception('La respuesta de Flow no contiene un token válido.');

    //     } catch (\Exception $e) {
    //         Log::error("Error en Checkout@process: " . $e->getMessage(), [
    //             'order_id' => $order->id
    //         ]);

    //         return redirect()->route('patient.orders')
    //             ->with('error', 'No pudimos conectar con la pasarela de pagos. Por favor, intenta más tarde.');
    //     }
    // }

        // CheckoutController.php
