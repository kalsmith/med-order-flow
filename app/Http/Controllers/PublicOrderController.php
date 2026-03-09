<?php

namespace App\Http\Controllers;

use App\Models\MedicalOrder;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PublicOrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'patient_name' => 'required|string|max:255',
            'patient_rut'  => 'required|string|max:15',
            'exam_type_id' => 'required|exists:exam_types,id',
        ]);

        // 1. Buscamos el médico comodín (Asegúrate de que exista al menos uno activo)
        $doctor = Doctor::where('is_active', true)->first();

        if (!$doctor) {
            return back()->with('error', 'Lo sentimos, no hay médicos disponibles para firmar órdenes en este momento.');
        }

        // 2. Lógica de Paciente "Al Vuelo"
        $patient = Patient::where('rut', $request->patient_rut)->first();

        if (!$patient) {
            // Limpiamos el RUT para el email (evitar caracteres raros)
            $rutClean = str_replace(['.', '-'], '', $request->patient_rut);

            // Creamos el User (Requerido por la FK de tu migración)
            $user = User::firstOrCreate(
                ['email' => strtolower($rutClean) . '@placeholder.cl'],
                [
                    'name'     => $request->patient_name,
                    'password' => Hash::make($request->patient_rut), // RUT como clave temporal
                ]
            );

            // Creamos el Paciente vinculado a ese User
            $patient = Patient::create([
                'user_id' => $user->id,
                'rut'     => $request->patient_rut,
            ]);
        }

        // 3. Crear la Orden (Asegúrate de que patient_id esté en el $fillable de MedicalOrder)
        $order = MedicalOrder::create([
            'patient_id'   => $patient->id,
            'doctor_id'    => $doctor->id,
            'exam_type_id' => $request->exam_type_id,
            'status'       => 'pending',
        ]);

        return back()->with('success', '¡Orden solicitada con éxito! N° de registro: ' . $order->id);
    }
}
