<?php

namespace App\Http\Controllers;

use App\Models\GatewayTransaction;
use App\Services\FlowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FlowController extends Controller
{
    protected $flowService;

    public function __construct(FlowService $flowService)
    {
        $this->flowService = $flowService;
    }

    // Webhook: Recibe la confirmación silenciosa de Flow
    public function confirmation(Request $request)
    {
        Log::info("¡WEBHOOK DETECTADO! Datos recibidos: ", $request->all());
        $token = $request->input('token');
        try {
            $this->flowService->handleWebhook($token);
            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error("FLOW WEBHOOK ERROR: " . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }


    public function returnUrl(Request $request)
{
    // 1. Recibimos el token de Flow
    $token = $request->query('token') ?? $request->input('token');
    if (!$token) return redirect()->route('home');

    // 2. Simplemente redirigimos a la ruta de estado
    // Esto mantiene la URL limpia y descriptiva
    return redirect()->route('payment.status', ['token' => $token]);
}

public function viewStatus($token)
{
    $gatewayTrx = GatewayTransaction::where('token', $token)->firstOrFail();

    // Consultamos a la API de Flow el estado real actual
    $flowStatus = $this->flowService->getPaymentStatus($token);

    // Mapeo de estados para la vista
    $config = match ((int)($flowStatus->status ?? 0)) {
        2 => [
            'status'  => 'success',
            'title'   => '¡Pago Confirmado!',
            'message' => 'Tu transacción ha sido exitosa. Ya puedes acceder a tu orden médica.',
            'action'  => route('patient.orders')
        ],
        3, 4 => [
            'status'  => 'error',
            'title'   => 'Pago Rechazado',
            'message' => 'Lo sentimos, el banco ha rechazado la transacción o esta ha sido anulada.',
            'action'  => route('home') // O a la ruta de solicitar orden
        ],
        default => [
            'status'  => 'pending',
            'title'   => 'Procesando Pago',
            'message' => 'Estamos confirmando la transacción con tu institución financiera...',
            'action'  => route('patient.orders')
        ],
    };

    // Si es rechazo, aprovechamos de actualizar nuestra BD
    if (in_array((int)$flowStatus->status, [3, 4])) {
        $gatewayTrx->update(['status' => 'rejected']);
    }

    return view('payments.flow.status', compact('config', 'gatewayTrx'));
}








    public function cancel()
    {
        return view('payments.flow.status', [
            'status' => 'info',
            'title' => 'Pago Cancelado',
            'message' => 'Has cancelado el proceso de pago.'
        ]);
    }
}
