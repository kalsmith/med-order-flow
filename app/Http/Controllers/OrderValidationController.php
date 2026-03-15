<?php

namespace App\Http\Controllers;

use App\Models\Prescription;

class OrderValidationController extends Controller
{
    public function show($id)
    {
        // Buscamos la receta
        $prescription = Prescription::where('verification_code', $id)
            ->with(['order.patient', 'doctor.user'])
            ->first();

        // Si no existe, enviamos a una vista de error amigable en lugar de fallar
        if (!$prescription) {
            return response()->view('orders.validation-error', [
                'code' => $id
            ], 404);
        }

        return view('orders.validation-success', [
            'order' => $prescription->order
        ]);
    }
}
