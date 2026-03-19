<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class PatientCircleController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Buscamos si el usuario ya tiene su perfil principal (self)
        $patient = $user->patients()->where('relationship', 'self')->first();

        // LÓGICA DE ONBOARDING:
        // Si no tiene perfil, lo mandamos a la vista de completar datos.
        // Reutilizamos la vista del flow pero con variables nulas para que no rompa.
        if (!$patient) {
            return view('front.flow.complete-profile', [
                'type' => 'circle_onboarding', // Un flag por si quieres personalizar el texto en la vista
                'id' => null,
                'exam_type' => null
            ]);
        }

        // Si ya tiene perfil, mostramos el micro-panel normal
        $members = $user->patients()->orderBy('is_primary', 'desc')->get();

        return view('front.patient.circle.index', compact('members'));
    }


    public function examHistory()
    {
        $patientIds = auth()->user()->patients()->pluck('id');
        $orders = Order::whereIn('patient_id', $patientIds)
            ->with(['patient', 'items'])
            ->whereIn('status', ['paid', 'completed', 'manual_review'])
            ->latest()
            ->get();

        return view('patient.exams.history', compact('orders'));
    }

}
