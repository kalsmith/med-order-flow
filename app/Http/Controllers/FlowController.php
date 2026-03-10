<?php

namespace App\Http\Controllers;

use App\Models\GatewayTransaction;
use App\Services\FlowService;
use Illuminate\Http\Request;

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
        // Asegúrate de que este método exista y no tenga errores de sintaxis
        $token = $request->query('token') ?? $request->input('token'); // Captura tanto GET como POST

        if (!$token) return redirect()->route('home');

        $gatewayTrx = GatewayTransaction::where('token', $token)->first();
        if (!$gatewayTrx) return redirect()->route('home');

        return redirect()->route('payment.success', ['order' => $gatewayTrx->medical_order_id]);
    }
}
