<?php

namespace App\Http\Controllers;

use App\Models\GatewayTransaction;
use App\Services\FlowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FlowController extends Controller
{
    public function confirmation(Request $request)
    {
        $token = $request->input('token');
        // Usamos el método que ya tenías bien construido en el Service
        app(FlowService::class)->handleWebhook($token);

        return response()->json(['status' => 'ok']);
    }

public function returnUrl(Request $request)
{
    $token = $request->query('token') ?? $request->input('token');

    // 1. Si no hay token, al home
    if (!$token) return redirect()->route('home');

    // 2. Buscamos la transacción
    $gatewayTrx = GatewayTransaction::where('token', $token)->first();

    // 3. VALIDACIÓN CRÍTICA:
    // Si no existe la transacción O el ID de la orden es nulo, logueamos el error y evitamos el crash
    if (!$gatewayTrx || empty($gatewayTrx->medical_order_id)) {
        Log::error("Flow Error en returnUrl: No se encontró orden asociada al token: " . $token);
        return redirect()->route('patient.orders')
                         ->with('error', 'Hubo un problema recuperando tu orden. Por favor contacta a soporte.');
    }

    // 4. Si todo está OK, redirigimos pasando el ID de la orden
    return redirect()->route('payment.success', ['order' => $gatewayTrx->medical_order_id]);
}
}
