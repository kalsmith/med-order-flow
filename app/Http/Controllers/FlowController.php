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

    /**
     * Webhook: Confirmación silenciosa de Flow.
     */
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

    /**
     * ReturnUrl: Redirige al estado estético.
     */
    public function returnUrl(Request $request)
    {
        $token = $request->query('token') ?? $request->input('token');
        if (!$token) return redirect()->route('home');

        return redirect()->route('payment.status', ['token' => $token]);
    }

    /**
     * ViewStatus: La cara visible para el paciente.
     */
    public function viewStatus($token)
    {
        $gatewayTrx = GatewayTransaction::where('token', $token)->firstOrFail();
        $order = Order::find($gatewayTrx->payable_id);

        $flowStatus = $this->flowService->getPaymentStatus($token);
        $flowStatusCode = (int)($flowStatus->status ?? 0);

        if ($order && ($order->status === 'refund_pending' || $order->status === 'manual_review')) {
            $config = [
                'status'  => 'warning',
                'title'   => 'Procesando Documentación',
                'message' => 'Tu pago fue recibido correctamente, pero estamos terminando de generar tu documentación médica. Estará disponible en unos minutos.',
                'action'  => route('patient.orders')
            ];
        } else {
            $config = match ($flowStatusCode) {
                2 => [
                    'status'  => 'success',
                    'title'   => '¡Pago Confirmado!',
                    'message' => 'Tu transacción ha sido exitosa. Ya puedes acceder a tus servicios desde tu panel.',
                    'action'  => route('patient.orders')
                ],
                3, 4 => [
                    'status'  => 'error',
                    'title'   => 'Pago No Completado',
                    'message' => 'Lo sentimos, la transacción ha sido rechazada o anulada por la institución financiera.',
                    'action'  => route('patient.orders')
                ],
                default => [
                    'status'  => 'pending',
                    'title'   => 'Validando con el Banco',
                    'message' => 'Estamos esperando la confirmación final de tu pago. Esto puede tardar unos segundos...',
                    'action'  => route('patient.orders')
                ],
            };
        }

        if (in_array($flowStatusCode, [3, 4])) {
            $gatewayTrx->update(['status' => 'rejected']);
        }

        return view('payments.flow.status', compact('config', 'gatewayTrx'));
    }

    /**
     * Cancelación: Si el usuario aborta.
     */
    public function cancel()
    {
        $config = [
            'status'  => 'info',
            'title'   => 'Pago Cancelado',
            'message' => 'Has cancelado el proceso de pago en la pasarela.',
            'action'  => route('patient.orders')
        ];
        return view('payments.flow.status', compact('config'));
    }
}
