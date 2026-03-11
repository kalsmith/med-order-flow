<?php

namespace App\Http\Controllers;

use App\Models\MedicalOrder;
use App\Services\FlowService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Inicia el proceso de pago redirigiendo a Flow
     */
    public function process(MedicalOrder $order, FlowService $flowService)
    {
        // Seguridad: Solo el dueño puede pagar su orden
        if ($order->patient->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return redirect()->route('patient.orders')->with('info', 'Esta orden ya fue procesada.');
        }

        $response = $flowService->createPayment($order);

        if ($response && isset($response->token)) {
            return redirect()->away($response->url . "?token=" . $response->token);
        }

        return back()->with('error', 'Error al conectar con la pasarela de pago.');
    }
}
