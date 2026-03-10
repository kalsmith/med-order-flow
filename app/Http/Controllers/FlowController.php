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

    if (!$token) return redirect()->route('home');

    // DEBUG: Registremos qué estamos buscando
    Log::info("Intentando buscar token: " . $token);

    // DEBUG: Verifiquemos si existe algún token en la tabla
    $totalRegistros = GatewayTransaction::count();
    Log::info("Total de registros en GatewayTransaction: " . $totalRegistros);

    $gatewayTrx = GatewayTransaction::where('token', $token)->first();

    if (!$gatewayTrx) {
        Log::error("TOKEN NO ENCONTRADO EN DB: " . $token);
        // Opcional: Loguear los últimos tokens guardados para comparar
        $ultimos = GatewayTransaction::latest()->limit(5)->pluck('token');
        Log::error("Tokens recientes en DB: " . $ultimos);

        return redirect()->route('patient.orders')
                         ->with('error', 'El token de pago no se encontró en el sistema.');
    }

    return redirect()->route('payment.success', ['order' => $gatewayTrx->payable_id]);
}


}
