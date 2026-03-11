<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use App\Models\MedicalOrder;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PatientOrderController extends Controller
{
    /**
     * Paso 1: El usuario eligió un Pack desde la Home.
     * El middleware 'auth' ya lo obligó a loguearse antes de llegar aquí.
     */
    public function confirmPack(ExamType $exam_type)
    {
        $user = Auth::user();

        // 1. Verificación de Perfil (RUT)
        // Si no tiene perfil 'self', lo mandamos a completar datos
        if (!$user->patients()->where('relationship', 'self')->exists()) {
            return redirect()->route('profile.complete')
                ->with('info', 'Para generar tu orden médica, primero necesitamos completar tu perfil legal.');
        }

        $patient = $user->patients()->where('relationship', 'self')->first();

        // Mostramos una vista de confirmación antes de crear la orden
        return view('front.orders.confirm-pack', compact('exam_type', 'patient'));
    }

    /**
     * Paso 1.B: El usuario quiere una orden personalizada (Texto libre).
     */
    public function customOrder()
    {
        $user = Auth::user();

        if (!$user->patients()->where('relationship', 'self')->exists()) {
            return redirect()->route('profile.complete');
        }

        return view('front.orders.custom-request');
    }

    /**
     * Paso 2: Crear la orden en la base de datos y saltar al pago.
     */
    public function store(Request $request)
    {
        $request->validate([
            'exam_type_id' => 'required_without:custom_description|exists:exam_types,id',
            'custom_description' => 'required_without:exam_type_id|string|min:10|max:1000',
        ]);

        $user = Auth::user();
        $patient = $user->patients()->where('relationship', 'self')->firstOrFail();

        // Asignamos un médico disponible (puedes rotarlos o tener uno fijo)
        $doctor = Doctor::where('is_active', true)->first();

        if (!$doctor) {
            return back()->with('error', 'Lo sentimos, no hay médicos disponibles en este momento. Intenta más tarde.');
        }

        $isCustom = $request->filled('custom_description');

        // Creamos la orden médica
        $order = MedicalOrder::create([
            'id'                 => (string) Str::uuid(),
            'patient_id'         => $patient->id,
            'doctor_id'          => $doctor->id,
            'exam_type_id'       => $isCustom ? null : $request->exam_type_id,
            'custom_description' => $request->custom_description,
            'type'               => $isCustom ? 'custom' : 'standard',
            'amount'             => $isCustom ? 9990 : ExamType::find($request->exam_type_id)->base_price,
            'status'             => 'pending', // Esperando pago
            'verification_code'  => strtoupper(Str::random(8)),
        ]);

        // Redirigimos al Checkout (Próximo controlador)
        return redirect()->route('checkout.index', $order->id);
    }

    /**
     * Listado de órdenes del paciente.
     */
    public function index()
    {
        $patient = Auth::user()->patients()->where('relationship', 'self')->first();
        $orders = $patient ? $patient->medicalOrders()->with('examType')->latest()->get() : collect();

        return view('patient.orders.index', compact('orders'));
    }

    public function download($orderId)
    {
        $order = MedicalOrder::findOrFail($orderId);

        // 1. Validar propiedad (Ya lo tienes)
        if ((int)auth()->id() !== (int)$order->patient->user->id) {
            abort(403);
        }

        // 2. Validar que esté firmada (Flujo Standard completado o Custom ya revisado)
        if ($order->status !== 'signed') {
            return back()->with('error', 'La orden aún no ha sido firmada por el médico.');
        }

        // 3. Generar/Retornar PDF (Aquí llamarías a tu lógica de PDF con la firma)
        return "Descargando Orden Médica #{$order->id} (Documento Firmado)";
    }

    public function showSuccess(MedicalOrder $order = null)
{
    // Si no viene orden (por si alguien entra manual), lo mandamos a la lista
    if (!$order) {
        return redirect()->route('patient.orders');
    }

    // Seguridad: Verificar que la orden sea del usuario logueado
    if ((string)$order->patient->user_id !== (string)auth()->id()) {
        abort(403);
    }

    // Cargamos relaciones para el comprobante
    $order->load(['patient.user', 'paymentTransaction']);

    return view('payment.success', compact('order'));
}

}
