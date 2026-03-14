<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\FlowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    /**
     * Inicia el proceso de pago.
     * La Orden ya viene con su 'type' y 'exam_type_id' desde el PatientOrderController.
     */
    public function process(Order $order, FlowService $flowService)
    {
        // 1. CARGA DE RELACIÓN (Eager Loading)
        $order->loadMissing('patient');

        // 2. SEGURIDAD: Solo el dueño puede pagar
        if ((string)$order->patient->user_id !== (string)auth()->id()) {
            Log::warning("Intento de pago no autorizado", ['order_id' => $order->id, 'user' => auth()->id()]);
            abort(403, 'No tienes permiso para pagar esta orden.');
        }

        // 3. VALIDACIÓN DE ESTADO: Solo se pagan órdenes pendientes
        if ($order->status !== 'pending') {
            return redirect()->route('patient.orders')
                ->with('info', 'Esta orden ya fue procesada o no está disponible.');
        }

        try {
            /**
             * 4. DISPARO A PASARELA
             * Le pasamos el objeto $order completo. El FlowService extraerá
             * el ID, el monto y el 'type' directamente del modelo.
             */
            $response = $flowService->createPayment($order);

            if ($response && isset($response->token)) {
                return redirect()->away($response->url . "?token=" . $response->token);
            }

            throw new \Exception('Pasarela Flow no respondió con un token de pago.');

        } catch (\Exception $e) {
            Log::error("Error Crítico Checkout@process: " . $e->getMessage(), ['order_id' => $order->id]);

            return redirect()->route('patient.orders')
                ->with('error', 'Error al conectar con el centro de pagos. Intente nuevamente.');
        }
    }
}
