<?php

namespace App\Http\Controllers;

use App\Models\GatewayTransaction;
use App\Models\MedicalOrder;
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


/**
 * Webhook que Flow llama cuando el reembolso cambia de estado
 */
public function refundConfirmation(Request $request)
{
    try {
        $token = $request->input('token');
        Log::info("WEBHOOK REEMBOLSO: Recibido token: " . $token);

        // 1. Consultar el estado real del reembolso en Flow
        // Debes implementar getRefundStatus en tu FlowService
        $statusData = $this->flowService->getRefundStatus($token);

        if ($statusData['status'] === 'refunded') { // El estado puede variar según la documentación (ej: 2)
            // 2. Buscar la orden relacionada (puedes usar el commerceOrder que devuelve Flow)
            $orderId = str_replace('REF-', '', $statusData['commerceOrder']);
            // El ID podría traer el timestamp que agregamos, límpialo si es necesario
            $orderId = explode('-', $orderId)[0];

            $order = MedicalOrder::find($orderId);
            if ($order) {
                $order->update(['status' => 'cancelled']);
                Log::info("WEBHOOK REEMBOLSO: Orden {$orderId} marcada como cancelada exitosamente.");
            }
        }

        return response('OK', 200);

    } catch (\Exception $e) {
        Log::error("WEBHOOK REEMBOLSO: Error procesando notificación: " . $e->getMessage());
        return response('Error', 500);
    }
}

}
