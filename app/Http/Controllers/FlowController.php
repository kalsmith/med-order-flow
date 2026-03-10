<?php

namespace App\Http\Controllers;

use App\Models\GatewayTransaction;
use App\Models\MedicalOrder;
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
     * Webhook principal de pago (Server-to-Server)
     */
public function confirmation(Request $request)
    {
        $token = $request->input('token');
        try {
            $this->flowService->handleWebhook($token);
            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error("FLOW WEBHOOK ERROR: " . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Retorno del usuario (Navegador)
     */
public function returnUrl(Request $request)
    {
        $token = $request->query('token') ?? $request->input('token');
        if (!$token) return redirect()->route('home');

        $gatewayTrx = GatewayTransaction::where('token', $token)->first();

        if (!$gatewayTrx) {
            return view('payments.flow.status', [
                'status' => 'error',
                'title' => 'Token no encontrado',
                'message' => 'No pudimos validar tu pago en nuestro sistema.'
            ]);
        }

        $order = MedicalOrder::find($gatewayTrx->payable_id);

        // Caso de Reembolso por error de firma
        if ($order && $order->status === 'refund_pending') {
            return view('payments.flow.status', [
                'status' => 'warning',
                'title' => 'Reembolso Iniciado',
                'message' => 'El pago fue recibido, pero hubo un error al generar la orden médica. Tu dinero está siendo devuelto automáticamente.'
            ]);
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

            // Consultar el estado detallado en Flow
            $statusData = $this->flowService->getRefundStatus($token);

            // Flow devuelve status 2 para reembolsos exitosos (según documentación estándar)
            // o el string 'refunded' dependiendo de la versión de la API
            if (isset($statusData['status']) && ($statusData['status'] == 2 || $statusData['status'] === 'refunded')) {

                // Extraemos el ID original de la orden desde el commerceOrder
                // El formato que enviamos fue: REF-{ID}-{TIMESTAMP}
                $parts = explode('-', $statusData['commerceOrder']);
                $orderId = $parts[1] ?? null;

                if ($orderId) {
                    $order = MedicalOrder::find($orderId);
                    if ($order) {
                        $order->update(['status' => 'cancelled']);
                        Log::info("WEBHOOK REEMBOLSO: Orden {$orderId} cancelada definitivamente tras reembolso exitoso.");
                    }
                }
            }

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error("WEBHOOK REEMBOLSO ERROR: " . $e->getMessage());
            return response('Error', 500);
        }
    }

    /**
     * Métodos de escape por si el usuario cancela o el pago falla en el portal de Flow
     */
public function cancel()
    {
        return view('payments.flow.status', [
            'status' => 'info',
            'title' => 'Pago Cancelado',
            'message' => 'Has cancelado el proceso de pago en el portal de Flow.'
        ]);
    }

    public function fail()
    {
        return view('payments.flow.status', [
            'status' => 'error',
            'title' => 'Pago Rechazado',
            'message' => 'La transacción fue rechazada por la pasarela de pago o el banco.'
        ]);
    }
}
