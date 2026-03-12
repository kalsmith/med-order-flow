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
    Log::info("=== INICIO PROCESO DE ORDEN ===", ['payload' => $request->all()]);

    // 1. Validación
    try {
        $request->validate([
            'patient_id'   => 'required|exists:patients,id',
            'exam_type_id' => 'required_unless:type,custom|exists:exam_types,id',
            'custom_description' => 'required_if:type,custom|min:10'
        ]);
        Log::info("1. Validación aprobada.");
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error("Fallo en validación:", ['errors' => $e->errors()]);
        throw $e;
    }

    try {
        $order = DB::transaction(function () use ($request) {

            $patient = Patient::findOrFail($request->patient_id);
            Log::info("2. Paciente encontrado:", ['id' => $patient->id, 'nombre' => $patient->full_name]);

            // DETERMINAR FLUJO
            if ($request->type === 'custom') {
                Log::info("3. Entrando a FLUJO CUSTOM.");

                $examId = null;
                $amount = 9990;
                $orderType = 'custom';
                $description = $request->custom_description;
                $doctor = null; // En custom, queda libre para el pool

                Log::info("4. Parámetros Custom listos. Doctor asignado: NULL (Pool abierto).");
            } else {
                Log::info("3. Entrando a FLUJO ESTÁNDAR.");

                $exam = ExamType::findOrFail($request->exam_type_id);
                $examId = $exam->id;
                $amount = $exam->base_price;
                $orderType = 'standard';
                $description = null;

                Log::info("4. Buscando doctor por especialidad...", ['specialty_id' => $exam->specialty_id]);
                $doctor = Doctor::getNextAvailableForSpecialty($exam->specialty_id);

                if (!$doctor) {
                    Log::error("ERROR: No se encontró doctor para la especialidad.");
                    throw new \Exception('No hay médicos disponibles para esta especialidad.');
                }
                Log::info("5. Doctor asignado por rotación:", ['id' => $doctor->id]);
            }

            // 3. CREAR LA ORDEN
            Log::info("6. Intentando crear registro en MedicalOrder...");
            $newOrder = MedicalOrder::create([
                'id'                => (string) \Illuminate\Support\Str::uuid(),
                'patient_id'        => $patient->id,
                'doctor_id'         => $doctor ? $doctor->id : null, // IMPORTANTE: Permitir null
                'exam_type_id'      => $examId,
                'custom_description'=> $description,
                'status'            => 'pending',
                'type'              => $orderType,
                'amount'            => $amount,
                'verification_code' => strtoupper(\Illuminate\Support\Str::random(8)),
            ]);

            Log::info("7. ORDEN CREADA EXITOSAMENTE:", ['order_id' => $newOrder->id]);

            // 4. ACTUALIZAR TURNO (Solo si hay doctor)
            if ($doctor) {
                Log::info("8. Actualizando turno del doctor.");
                $doctor->update(['last_assigned_at' => now()]);
            } else {
                Log::info("8. Sin doctor que actualizar (es flujo custom).");
            }

            return $newOrder;
        });

        Log::info("=== FIN PROCESO EXITOSO - REDIRIGIENDO AL PAGO ===");
        return redirect()->route('checkout.process', ['order' => $order->id]);

    } catch (\Exception $e) {
        Log::error("!!! EXCEPCIÓN EN STORE !!!", [
            'mensaje' => $e->getMessage(),
            'linea'   => $e->getLine(),
            'archivo' => $e->getFile()
        ]);
        return back()->with('error', 'Error al procesar la orden: ' . $e->getMessage());
    }
}


    /**
     * Listado de órdenes del paciente.
     */
public function index()
{
    // 1. Obtenemos los IDs de TODOS los pacientes asociados a este usuario
    $patientIds = auth()->user()->patients()->pluck('id');

    if ($patientIds->isEmpty()) {
        return redirect()->route('home')->with('error', 'No se encontraron perfiles de paciente.');
    }

    // 2. Buscamos órdenes donde el patient_id esté en esa lista de IDs
    $orders = MedicalOrder::whereIn('patient_id', $patientIds)
        ->with(['examType.specialty', 'patient']) // Cargamos 'patient' para saber de quién es la orden
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
