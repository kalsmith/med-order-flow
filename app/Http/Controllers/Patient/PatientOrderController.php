<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use App\Models\MedicalOrder;
use App\Models\Doctor;
use App\Models\Order;
use App\Models\Patient;
use App\Models\Prescription;
use App\Services\OrderPdfService;
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

public function index()
{
    $orders = auth()->user()->patient->orders()->latest()->get();
    return view('patient.orders.index', compact('orders'));
}

public function store(Request $request)
{
    Log::info("=== INICIO CREACIÓN DE ORDEN ===", ['payload' => $request->all()]);

    // 1. NORMALIZACIÓN: Determinamos el tipo antes de validar
    if (!$request->has('type')) {
        $request->merge(['type' => $request->has('exam_type_id') ? 'standard' : 'custom']);
    }

    // 2. VALIDACIÓN
    $request->validate([
        'patient_id'         => 'required|exists:patients,id',
        'exam_type_id'       => 'required_unless:type,custom|nullable|exists:exam_types,id',
        'custom_description' => 'required_if:type,custom|nullable|string|min:10',
        'clinical_context'   => 'nullable|string'
    ]);

    try {
        $order = DB::transaction(function () use ($request) {
            $orderType = $request->type;
            $amount = 0;
            $examId = null;

            // 3. DETERMINACIÓN DE COSTOS (Fuente de verdad)
            if ($orderType === 'custom') {
                $amount = 9990;
                $examId = null;
            } else {
                $exam = ExamType::findOrFail($request->exam_type_id);
                $amount = $exam->base_price;
                $examId = $exam->id;
            }

            // 4. CREACIÓN DE LA ORDEN COMERCIAL
            // Importante: No pasamos 'verification_code' ni 'doctor_id' aquí.
            // La orden nace como un documento de venta puro.
            return Order::create([
                'id'                 => (string) Str::uuid(),
                'patient_id'         => $request->patient_id,
                'exam_type_id'       => $examId,
                'type'               => $orderType,
                'amount'             => $amount,
                'status'             => 'pending',
                'custom_description' => $request->custom_description,
                'clinical_context'   => $request->clinical_context,
            ]);
        });

        Log::info("Orden Comercial Creada con éxito", ['order_id' => $order->id]);

        // 5. REDIRECCIÓN AL PROCESO DE PAGO
        // Usamos 'checkout.process' que es el que dispara hacia Flow
        return redirect()->route('checkout.process', ['order' => $order->id]);

    } catch (\Exception $e) {
        Log::error("Error Crítico en PatientOrderController@store: " . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);

        return back()->with('error', 'Ocurrió un error al generar tu orden: ' . $e->getMessage());
    }
}


    /**
     * Listado de órdenes del paciente.
     */

public function download($orderId, OrderPdfService $pdfService)
{
    // 1. Cargar la orden con las relaciones necesarias para evitar queries extras
    $order = MedicalOrder::with(['patient.user', 'doctor.user'])->findOrFail($orderId);

    // 2. Validar propiedad
    if ((int)auth()->id() !== (int)$order->patient->user->id) {
        abort(403);
    }

    // 3. Validar que esté firmada (Estado 'signed') [cite: 2, 28]
    if ($order->status !== 'signed') {
        return back()->with('error', 'La orden aún no ha sido firmada por el médico.');
    }

    // 4. Generar el PDF usando el servicio
    $pdf = $pdfService->generate($order);

    // 5. Formatear nombre de archivo profesional (ej: Orden_Benjamin_de_la_Fuente.pdf)
    $fileName = 'Orden_' . Str::slug($order->patient->full_name, '_') . '.pdf';

    return $pdf->download($fileName);
}


public function showSuccess(Order $order = null)
    {
        if (!$order) return redirect()->route('patient.orders');

        if ((string)$order->patient->user_id !== (string)auth()->id()) {
            abort(403);
        }

        $order->load(['patient.user', 'prescriptions']);

        return view('payments.payment-success', compact('order'));
    }




}



//     public function showSuccess(MedicalOrder $order = null)
// {
//     // Si no viene orden (por si alguien entra manual), lo mandamos a la lista
//     if (!$order) {
//         return redirect()->route('patient.orders');
//     }

//     // Seguridad: Verificar que la orden sea del usuario logueado
//     if ((string)$order->patient->user_id !== (string)auth()->id()) {
//         abort(403);
//     }

//     // Cargamos relaciones para el comprobante
//     $order->load(['patient.user', 'paymentTransaction']);

// return view('payments.payment-success', compact('order'));
// }


// public function store(Request $request)
// {
//     Log::info("=== INICIO PROCESO DE ORDEN ===", ['payload' => $request->all()]);

//     if (!$request->has('type')) {
//         $request->merge(['type' => $request->has('exam_type_id') ? 'standard' : 'custom']);
//     }
//     // 1. Validación
//     try {
//         $request->validate([
//             'patient_id'   => 'required|exists:patients,id',
//             'exam_type_id' => 'required_unless:type,custom|exists:exam_types,id',
//             'custom_description' => 'required_if:type,custom|min:10'
//         ]);
//         Log::info("1. Validación aprobada.");
//     } catch (\Illuminate\Validation\ValidationException $e) {
//         Log::error("Fallo en validación:", ['errors' => $e->errors()]);
//         throw $e;
//     }

//     try {
//         $order = DB::transaction(function () use ($request) {

//             $patient = Patient::findOrFail($request->patient_id);
//             //Log::info("2. Paciente encontrado:", ['id' => $patient->id, 'nombre' => $patient->full_name]);

//             // DETERMINAR FLUJO
//             if ($request->type === 'custom') {
//                 //Log::info("3. Entrando a FLUJO CUSTOM.");

//                 $examId = null;
//                 $amount = 9990;
//                 $orderType = 'custom';
//                 $description = $request->custom_description;
//                 $doctor = null; // En custom, queda libre para el pool

//                 //Log::info("4. Parámetros Custom listos. Doctor asignado: NULL (Pool abierto).");
//             } else {
//                 //Log::info("3. Entrando a FLUJO ESTÁNDAR.");

//                 $exam = ExamType::findOrFail($request->exam_type_id);
//                 $examId = $exam->id;
//                 $amount = $exam->base_price;
//                 $orderType = 'standard';
//                 $description = null;

//                 //Log::info("4. Buscando doctor por especialidad...", ['specialty_id' => $exam->specialty_id]);
//                 $doctor = Doctor::getNextAvailableForSpecialty($exam->specialty_id);

//                 if (!$doctor) {
//                     //Log::error("ERROR: No se encontró doctor para la especialidad.");
//                     throw new \Exception('No hay médicos disponibles para esta especialidad.');
//                 }
//                 //Log::info("5. Doctor asignado por rotación:", ['id' => $doctor->id]);
//             }

//             // 3. CREAR LA ORDEN
//             Log::info("6. Intentando crear registro en MedicalOrder...");
//             $newOrder = MedicalOrder::create([
//                 'id'                => (string) Str::uuid(),
//                 'patient_id'        => $patient->id,
//                 'doctor_id'         => $doctor ? $doctor->id : null, // IMPORTANTE: Permitir null
//                 'exam_type_id'      => $examId,
//                 'custom_description'=> $description,
//                 'status'            => 'pending',
//                 'type'              => $orderType,
//                 'amount'            => $amount,
//                 'verification_code' => strtoupper(Str::random(8)),
//             ]);

//             Log::info("7. ORDEN CREADA EXITOSAMENTE:", ['order_id' => $newOrder->id]);

//             // 4. ACTUALIZAR TURNO (Solo si hay doctor)
//             if ($doctor) {
//                 //Log::info("8. Actualizando turno del doctor.");
//                 $doctor->update(['last_assigned_at' => now()]);
//             } else {
//                 //Log::info("8. Sin doctor que actualizar (es flujo custom).");
//             }

//             return $newOrder;
//         });

//         Log::info("=== FIN PROCESO EXITOSO - REDIRIGIENDO AL PAGO ===");
//         return redirect()->route('checkout.process', ['order' => $order->id]);

//     } catch (\Exception $e) {
//         Log::error("!!! EXCEPCIÓN EN STORE !!!", [
//             'mensaje' => $e->getMessage(),
//             'linea'   => $e->getLine(),
//             'archivo' => $e->getFile()
//         ]);
//         return back()->with('error', 'Error al procesar la orden: ' . $e->getMessage());
//     }
// }


// public function index()
// {
//     // 1. Obtenemos los IDs de TODOS los pacientes asociados a este usuario
//     $patientIds = auth()->user()->patients()->pluck('id');

//     if ($patientIds->isEmpty()) {
//         return redirect()->route('home')->with('error', 'No se encontraron perfiles de paciente.');
//     }

//     // 2. Buscamos órdenes donde el patient_id esté en esa lista de IDs
//     $orders = MedicalOrder::whereIn('patient_id', $patientIds)
//         ->with(['examType.specialty', 'patient']) // Cargamos 'patient' para saber de quién es la orden
//         ->orderBy('created_at', 'desc')
//         ->paginate(10);

//     return view('patient.orders.index', compact('orders'));
// }
