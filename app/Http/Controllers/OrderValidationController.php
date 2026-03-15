<?php

namespace App\Http\Controllers;

use App\Models\MedicalOrder;
use App\Models\Prescription;

class OrderValidationController extends Controller
{
    public function show($id) // $id aquí recibirá el verification_code
    {
        $prescription = Prescription::where('verification_code', $id)
            ->with(['order', 'doctor'])
            ->firstOrFail(); // Si no existe el código, ahí sí da 404 correctamente

        return view('orders.validation-success', ['order' => $prescription->order]);
    }
}
