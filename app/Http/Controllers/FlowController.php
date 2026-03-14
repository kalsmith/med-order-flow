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

    // Obtenemos la orden médica relacionada
    $order = \App\Models\MedicalOrder::find($gatewayTrx->payable_id);

    // Consultamos a la API de Flow
    $flowStatus = $this->flowService->getPaymentStatus($token);
    $flowStatusCode = (int)($flowStatus->status ?? 0);

    // LÓGICA DE MAPEO DINÁMICO
    if ($order && $order->status === 'refund_pending') {
        // CASO ESPECIAL: Pago exitoso en Flow pero error interno (ej: falló la firma)
        $config = [
            'status'  => 'error', // Lo mostramos como error aunque se haya cobrado
            'title'   => 'Error en Procesamiento',
            'message' => 'Tu pago fue recibido, pero detectamos un error al generar la firma médica. <strong>Se ha gestionado un reembolso automático a tu cuenta.</strong>',
            'action'  => route('home')
        ];
    } else {
        // FLUJO NORMAL
        $config = match ($flowStatusCode) {
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
                'action'  => route('order.flow', ['type' => 'standard'])
            ],
            default => [
                'status'  => 'pending',
                'title'   => 'Procesando Pago',
                'message' => 'Estamos confirmando la transacción con tu institución financiera...',
                'action'  => route('patient.orders')
            ],
        };
    }

    if (in_array($flowStatusCode, [3, 4])) {
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
