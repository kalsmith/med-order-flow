<?php

namespace App\Http\Controllers;

use App\Models\MedicalOrder;
use App\Services\FlowService;

class CheckoutController extends Controller
{
    public function index($orderId)
    {
        $order = MedicalOrder::findOrFail($orderId);
        return view('checkout.index', compact('order'));
    }

    public function process($orderId)
    {
        $order = MedicalOrder::findOrFail($orderId);
        $flowService = app(FlowService::class);

        // El servicio se encarga de crear la transacción y llamar a Flow
        $response = $flowService->createPayment($order);

        if ($response && isset($response->token)) {
            return redirect()->away($response->url . "?token=" . $response->token);
        }

        return back()->with('error', 'No se pudo conectar con el sistema de pagos.');
    }
}
