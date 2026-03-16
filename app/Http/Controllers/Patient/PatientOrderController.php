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

