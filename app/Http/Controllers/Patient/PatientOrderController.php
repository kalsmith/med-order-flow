<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use App\Models\MedicalOrder;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    dd($request->all());
    // 1. Validación de entrada
    $request->validate([
        'exam_type_id' => 'required|exists:exam_types,id',
        'patient_id'   => 'required|exists:patients,id'
    ]);

    try {
        $order =  DB::transaction(function () use ($request) {
            $exam = ExamType::findOrFail($request->exam_type_id);
            $patient = Patient::findOrFail($request->patient_id);

            // 2. EJECUTAR MOTOR DE ROTACIÓN
            // Usamos la función que ya escribimos en el Modelo Doctor
            $doctor = Doctor::getNextAvailableForSpecialty($exam->specialty_id);

            if (!$doctor) {
                throw new \Exception('No hay médicos disponibles para esta especialidad en este momento.');
            }

            // 3. CREAR LA ORDEN ASIGNANDO AL DOCTOR GANADOR
            $newOrder = MedicalOrder::create([
                'id'                => (string) \Illuminate\Support\Str::uuid(),
                'patient_id'        => $patient->id,
                'doctor_id'         => $doctor->id, // Aquí queda guardado el Doctor 1 o 2 según toque
                'exam_type_id'      => $exam->id,
                'status'            => 'pending',
                'type'              => 'standard',
                'amount'            => $exam->base_price,
                'verification_code' => strtoupper(\Illuminate\Support\Str::random(8)),
            ]);

            // 4. ACTUALIZAR TURNO: El doctor seleccionado pasa al final de la cola
            $doctor->update(['last_assigned_at' => now()]);

            return $newOrder;
        });

        // 5. REDIRIGIR AL PROCESO DE PAGO (CheckoutController)
        return redirect()->route('checkout.process', ['order' => $order->id]);

    } catch (\Exception $e) {
        \Log::error("Error en Rotación/Creación de Orden: " . $e->getMessage());
        return back()->with('error', $e->getMessage());
    }
}


    /**
     * Listado de órdenes del paciente.
     */
public function index()
{
    $patient = auth()->user()->patients()->where('relationship', 'self')->first();

    if (!$patient) {
        return redirect()->route('home')->with('error', 'Perfil de paciente no encontrado.');
    }

    // Corregido: Cargamos examType y su relación specialty anidada
    $orders = MedicalOrder::where('patient_id', $patient->id)
        ->with(['examType.specialty'])
        ->orderBy('created_at', 'desc')
        ->paginate(10);

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

return view('payments.payment-success', compact('order'));
}

}
