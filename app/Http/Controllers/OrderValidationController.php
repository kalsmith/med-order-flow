<?php

namespace App\Http\Controllers;

use App\Models\MedicalOrder;

class OrderValidationController extends Controller
{
    public function show($id)
    {
        $order = MedicalOrder::with(['patient', 'doctor'])->findOrFail($id);

        // Si la orden no está firmada, no es válida para terceros
        if ($order->status !== 'signed') {
            return view('orders.validation-failed');
        }

        return view('orders.validation-success', compact('order'));
    }
}
