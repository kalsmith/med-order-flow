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
        $token = $request->input('token');
        try {
            $this->flowService->handleWebhook($token);
            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error("FLOW WEBHOOK ERROR: " . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }

    // Return: Donde aterriza el usuario tras pagar
    public function returnUrl(Request $request)
    {
        $token = $request->query('token') ?? $request->input('token');
        if (!$token) return redirect()->route('home');

        $gatewayTrx = GatewayTransaction::where('token', $token)->first();

        if (!$gatewayTrx) {
            return view('payments.flow.status', ['status' => 'error', 'title' => 'Error', 'message' => 'Transacción no encontrada.']);
        }

        // Redirigimos a la vista de éxito final de la orden
        return redirect()->route('payment.success', ['order' => $gatewayTrx->payable_id]);
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
