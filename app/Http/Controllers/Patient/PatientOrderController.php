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
use Illuminate\Validation\ValidationException;

class PatientOrderController extends Controller
{


    public function showSuccess(Order $order = null)
    {
        if (!$order) return redirect()->route('patient.orders');

        if ((string)$order->patient->user_id !== (string)auth()->id()) {
            abort(403);
        }

        $order->load(['patient.user', 'prescriptions']);

        return view('payments.payment-success', compact('order'));
    }


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
    $user = auth()->user();

    // Buscamos el paciente vinculado a este usuario
    // Si la relación $user->patient falla, lo buscamos manualmente
    $patient = Patient::where('user_id', $user->id)->first();

    if (!$patient) {
        // Log para debugear: así sabrás qué ID de paciente tiene el usuario 30 en realidad
        Log::error("Usuario {$user->id} no tiene entrada en tabla patients");
        return redirect()->route('home')->with('error', 'Perfil de paciente no encontrado.');
    }

    // Traemos las órdenes del paciente encontrado
    $orders = Order::where('patient_id', $patient->id)
        ->with(['examType', 'activePrescription'])
        ->latest()
        ->get();

    return view('patient.orders.index', compact('orders'));
}

public function store(Request $request)
{
    // Log::info(" [DEBUG-ORDER] === INICIO PROCESO STORE ===");
    // Log::info(" [DEBUG-ORDER] Payload Recibido:", $request->all());

    // 1. NORMALIZACIÓN
    if ($request->filled('exam_type_id')) {
        // Log::info(" [DEBUG-ORDER] Detectado exam_type_id ({$request->exam_type_id}). Forzando type a 'standard'.");
        $request->merge(['type' => 'standard']);
    }

    if ($request->type === 'multiple' && $request->filled('exam_ids')) {
        // Log::info(" [DEBUG-ORDER] Flujo MULTIPLE detectado. IDs: " . $request->exam_ids);
        $ids = explode(',', $request->exam_ids);
        $examNames = ExamType::whereIn('id', $ids)->pluck('name')->implode(', ');

        $request->merge([
            'custom_description' => "Solicitud de exámenes: " . $examNames
        ]);
        // Log::info(" [DEBUG-ORDER] custom_description generada para múltiple: " . $examNames);
    }
    elseif (!$request->has('type')) {
        // Log::info(" [DEBUG-ORDER] Sin type definido. Asignando 'custom'.");
        $request->merge(['type' => 'custom']);
    }

    // 2. VALIDACIÓN
    try {
        $request->validate([
            'patient_id'         => 'required|exists:patients,id',
            'exam_type_id'       => 'required_unless:type,custom,multiple|nullable|exists:exam_types,id',
            'custom_description' => 'required_if:type,custom,multiple|nullable|string|min:5',
            'clinical_context'   => 'nullable|string'
        ]);
        // Log::info(" [DEBUG-ORDER] Validación PASADA.");

    } catch (ValidationException $ve) {
        // Log::error(" [DEBUG-ORDER] Error de Validación:", $ve->errors());
        throw $ve;
    }


    try {
        // Log::info(" [DEBUG-ORDER] Iniciando Transacción DB...");
        $order = DB::transaction(function () use ($request) {
            $orderType = $request->type;
            $examId = $request->exam_type_id;
            $amount = 0;

            // Determinar monto base de la orden
            if ($orderType === 'custom' || $orderType === 'multiple') {
                $amount = 9990;
                $examId = null;
                // Log::info(" [DEBUG-ORDER] Precio fijo asignado: 9990 (Flujo {$orderType})");
            } else {
                $exam = ExamType::findOrFail($request->exam_type_id);
                $amount = $exam->base_price;
                // Log::info(" [DEBUG-ORDER] Precio catálogo asignado: {$amount} (Examen: {$exam->name})");
            }

            // Crear la Orden Principal
            $newOrder = Order::create([
                'id'                 => (string) Str::uuid(),
                'patient_id'         => $request->patient_id,
                'exam_type_id'       => $examId,
                'type'               => $orderType,
                'amount'             => $amount,
                'status'             => 'pending',
                'custom_description' => $request->custom_description,
                'clinical_context'   => $request->clinical_context,
            ]);

            // Log::info(" [DEBUG-ORDER] Orden creada: {$newOrder->id}. Iniciando creación de ítems...");

            // 3. POBLAR TABLA order_items (Historial Detallado)
            if ($orderType === 'multiple' && $request->filled('exam_ids')) {
                // Caso: Pack armado manualmente por el paciente
                $ids = explode(',', $request->exam_ids);
                $selectedExams = ExamType::whereIn('id', $ids)->get();

                foreach ($selectedExams as $item) {
                    $newOrder->items()->create([
                        'exam_type_id' => $item->id,
                        'exam_name'    => $item->name,
                    ]);
                    // Log::info(" [DEBUG-ORDER] Item agregado (Múltiple): {$item->name}");
                }
            }
            elseif ($orderType === 'standard' && $examId) {
                // Caso: Pack de plataforma o Examen único
                $parentExam = ExamType::with('children')->findOrFail($examId);

                if ($parentExam->children->isNotEmpty()) {
                    // Log::info(" [DEBUG-ORDER] El examen es un Pack/Batería. Desglosando hijos...");
                    foreach ($parentExam->children as $child) {
                        $newOrder->items()->create([
                            'exam_type_id' => $child->id,
                            'exam_name'    => $child->name,
                        ]);
                        // Log::info(" [DEBUG-ORDER] Item agregado (Hijo de Pack): {$child->name}");
                    }
                } else {
                    // Examen simple
                    $newOrder->items()->create([
                        'exam_type_id' => $parentExam->id,
                        'exam_name'    => $parentExam->name,
                    ]);
                    // Log::info(" [DEBUG-ORDER] Item agregado (Simple): {$parentExam->name}");
                }
            }
            // Nota: Para 'custom', la tabla queda vacía hasta aprobación médica.

            return $newOrder;
        });

        // Log::info(" [DEBUG-ORDER] === ÉXITO === Transacción finalizada para Orden ID: {$order->id}");
        return redirect()->route('checkout.process', ['order' => $order->id]);

    } catch (\Exception $e) {
        // Log::error(" [DEBUG-ORDER] !!! ERROR CRÍTICO EN STORE !!! " . $e->getMessage());
        // Log::error($e->getTraceAsString());
        return back()->with('error', 'Error al generar la orden: ' . $e->getMessage());
    }
}



    /**
     * Listado de órdenes del paciente.
     */
public function download(Order $order, OrderPdfService $pdfService)
    {
        // Seguridad: Solo el dueño de la orden puede descargarla
        if ((string)$order->patient->user_id !== (string)auth()->id()) {
            abort(403);
        }

        // Verificar que tenga receta firmada
        if (!$order->activePrescription) {
            return back()->with('error', 'La orden aún no ha sido firmada por un médico.');
        }

        $pdf = $pdfService->generate($order);

        $filename = 'Orden_Medica_' . $order->activePrescription->correlative_number . '.pdf';

        return $pdf->download($filename);
    }





}

