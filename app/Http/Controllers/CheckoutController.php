<?php

namespace App\Http\Controllers;

use App\Models\MedicalOrder;
use App\Services\FlowService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    protected $flow;

    public function __construct(FlowService $flow)
    {
        $this->flow = $flow;
    }

    public function processMedicalOrderPayment(MedicalOrder $order)
    {
        $response = $this->flow->createPayment($order);

        if ($response && isset($response->token)) {
            return redirect()->away($response->url . "?token=" . $response->token);
        }

        return back()->with('error', 'Error al procesar el pago con Flow.');
    }

    public function handleWebhook(Request $request)
    {
        $processed = $this->flow->handleWebhook($request->token);

        return $processed
            ? response('OK', 200)
            : response('Error', 400);
    }

    public function flowReturn(Request $request)
    {
        return redirect()->route('admin.orders.index')
            ->with('success', 'Pago recibido correctamente.');
    }
}
