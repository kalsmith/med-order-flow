<?php

namespace App\Http\Controllers;

use App\Models\Order;
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

    public function confirmation(Request $request)
    {
        $token = $request->input('token');
        try {
            $this->flowService->handleWebhook($token);
            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error("FLOW WEBHOOK ERROR: " . $e->getMessage());
            return response()->json(['status' => 'error'], 400);
        }
    }

    public function returnUrl(Request $request)
    {
        $token = $request->query('token') ?? $request->input('token');
        if (!$token) return redirect()->route('home');

        return redirect()->route('payment.status', ['token' => $token]);
    }

    public function viewStatus($token)
    {
        $gatewayTrx = GatewayTransaction::where('token', $token)->firstOrFail();
        $order = Order::find($gatewayTrx->payable_id);

        $flowStatus = $this->flowService->getPaymentStatus($token);
        $flowStatusCode = (int)($flowStatus->status ?? 0);

        // Mapeo dinámico corregido
        if ($order && ($order->status === 'refund_pending' || $order->status === 'manual_review')) {
            $config = [
                'status'  => 'warning',
                'title'   => 'Procesando Documentación',
                'message' => 'Tu pago fue recibido, pero estamos terminando de generar tu documentación médica. Estará disponible en unos minutos.',
                'action'  => route('patient.orders')
            ];
        } else {
            $config = match ($flowStatusCode) {
                2 => [
                    'status'  => 'success',
                    'title'   => '¡Pago Confirmado!',
                    'message' => 'Tu transacción ha sido exitosa. Ya puedes acceder a tus servicios.',
                    'action'  => route('patient.orders')
                ],
                3, 4 => [
                    'status'  => 'error',
                    'title'   => 'Pago No Completado',
                    'message' => 'Lo sentimos, la transacción ha sido rechazada o anulada.',
                    'action'  => route('patient.orders')
                ],
                default => [
                    'status'  => 'pending',
                    'title'   => 'Validando Pago',
                    'message' => 'Estamos esperando la confirmación final de tu banco...',
                    'action'  => route('patient.orders')
                ],

        };

        if (in_array($flowStatusCode, [3, 4])) {
            $gatewayTrx->update(['status' => 'rejected']);
        }

        // Asegúrate de que la carpeta sea 'payments.flow.status' o 'payment.status' según tu proyecto
        return view('payments.flow.status', compact('config', 'gatewayTrx'));
    }

    public function cancel()
    {
        $config = [
            'status'  => 'info',
            'title'   => 'Pago Cancelado',
            'message' => 'Has cancelado el proceso de pago.',
            'action'  => route('patient.orders')
        ];
        return view('payments.flow.status', compact('config'));
    }
}
