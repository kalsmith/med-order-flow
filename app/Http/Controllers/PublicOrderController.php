<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\ExamType;
use App\Models\MedicalOrder;
use App\Models\Doctor;
use App\Services\FlowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PublicOrderController extends Controller
{
    protected $flow;

    public function __construct(FlowService $flow)
    {
        $this->flow = $flow;
    }

    /**
     * Muestra el formulario para orden personalizada.
     * Accesible después de Google Login pero antes de completar perfil.
     */
    public function customOrder()
    {
        return view('orders.custom');
    }

    /**
     * Listado de órdenes del paciente
     */
    public function index()
    {
        $orders = Auth::user()->patient->medicalOrders()->latest()->get();
        return view('patient.orders.index', compact('orders'));
    }

    /**
     * Formulario de Perfil
     */
    public function completeProfileForm()
    {
        if (Auth::user()->patient) return redirect()->route('home');
        return view('front.complete-profile');
    }

    /**
     * Guardar Perfil y Sincronizar con Usuario
     */
    public function storeProfile(Request $request)
    {
        $request->validate([
            'full_name'       => 'required|string|min:8',
            'rut'             => 'required|string',
            'birth_date'      => 'required|date',
            'gender_biologic' => 'required|in:M,F',
            'custom_exam_name'=> 'nullable|string' // Capturamos si viene del flujo "A medida"
        ]);

        // Si viene con una solicitud de examen especial, la guardamos en sesión
        if ($request->has('custom_exam_name')) {
            session(['pending_custom_exam' => $request->custom_exam_name]);
        }

        DB::transaction(function () use ($request) {
            $user = Auth::user();
            $user->update(['name' => $request->full_name]);

            Patient::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name'       => $request->full_name,
                    'rut'             => preg_replace('/[^k0-9]/i', '', $request->rut),
                    'birth_date'      => $request->birth_date,
                    'gender_biologic' => $request->gender_biologic,
                    'prevision'       => 'Particular'
                ]
            );
        });

        // PRIORIDAD 1: Si pidió un examen personalizado
        if (session()->has('pending_custom_exam')) {
            return redirect()->route('orders.custom.confirm');
        }

        // PRIORIDAD 2: Si venía de un Pack estándar
        if (session()->has('pending_exam_id')) {
            $examId = session()->pull('pending_exam_id');
            return redirect()->route('orders.confirm', $examId);
        }

        return redirect()->route('home')->with('success', 'Perfil completado.');
    }

    /**
     * Vista previa para la orden personalizada (una vez que ya tiene perfil)
     */
    public function confirmCustomOrder()
    {
        $patient = Auth::user()->patient;
        $customExam = session('pending_custom_exam');

        if (!$customExam) return redirect()->route('home');

        return view('front.confirm-custom-order', [
            'patient' => $patient,
            'description' => $customExam,
            'price' => 9990 // Precio base para revisiones manuales
        ]);
    }

    /**
     * Vista previa antes de pagar (Pack estándar)
     */
    public function confirmOrder(ExamType $exam_type)
    {
        $patient = Auth::user()->patient;
        return view('front.confirm-order', ['exam' => $exam_type, 'patient' => $patient]);
    }

    /**
     * Generar Orden Final e Iniciar Salto a Pasarela
     */
    public function store(Request $request)
    {
        // Soporta tanto exam_type_id (estándar) como custom_description (especial)
        $request->validate([
            'exam_type_id' => 'required_without:custom_description|exists:exam_types,id',
            'custom_description' => 'required_without:exam_type_id|string'
        ]);

        $doctor = Doctor::where('is_active', true)->first();
        if (!$doctor) return back()->with('error', 'No hay médicos disponibles.');

        try {
            $order = DB::transaction(function () use ($request, $doctor) {
                $amount = 9990; // Default para custom
                $examId = null;
                $type = 'custom';

                if ($request->exam_type_id) {
                    $exam = ExamType::find($request->exam_type_id);
                    $amount = $exam->base_price;
                    $examId = $exam->id;
                    $type = 'standard';
                }

                return MedicalOrder::create([
                    'id'                => (string) Str::uuid(),
                    'patient_id'        => Auth::user()->patient->id,
                    'doctor_id'         => $doctor->id,
                    'exam_type_id'      => $examId,
                    'custom_description'=> $request->custom_description, // Debes tener este campo en tu migración
                    'status'            => 'pending',
                    'type'              => $type,
                    'amount'            => $amount,
                    'verification_code' => strtoupper(Str::random(8)),
                ]);
            });

            session()->forget(['pending_custom_exam', 'pending_exam_id']);
            return $this->processFlowPayment($order);

        } catch (\Exception $e) {
            Log::error("Error creando orden: " . $e->getMessage());
            return back()->with('error', 'Error al procesar la solicitud.');
        }
    }






    /**
     * Método para reintentar el pago de una orden existente
     * Útil desde la vista /mis-ordenes
     */
    public function retryPayment(MedicalOrder $order)
    {
        // Seguridad: Solo el dueño puede pagar y solo si está pendiente
        if ($order->patient_id !== Auth::user()->patient->id || $order->status !== 'pending') {
            return redirect()->route('patient.orders')->with('error', 'Esta orden no puede ser procesada.');
        }

        return $this->processFlowPayment($order);
    }

    /**
     * Lógica compartida para saltar a Flow
     */
    private function processFlowPayment(MedicalOrder $order)
    {
        $flowResponse = $this->flow->createPayment($order);

        // DEBUG TEMPORAL: Si esto se ejecuta, detendrá la app y mostrará la respuesta de Flow
        // dd($flowResponse);

        if ($flowResponse && isset($flowResponse->token)) {
            $url = $flowResponse->url . "?token=" . $flowResponse->token;
            return redirect()->away($url);
        }

        // Si entra aquí es porque Flow no devolvió token. Logueamos el error:
        Log::error("Flow no generó token para la orden: " . $order->id, ['response' => $flowResponse]);

        return redirect()->route('patient.orders')
            ->with('warning', 'La orden se guardó, pero la pasarela de pago no respondió.');
    }


public function download($orderId)
{
    $order = MedicalOrder::with(['patient.user', 'doctor.user'])->findOrFail($orderId);

    // Convertimos todo a (int) para evitar errores de tipo string vs integer
    $currentUserId = (int) auth()->id();
    $ownerUserId   = (int) ($order->patient->user->id ?? 0);

    // Asumiendo que doctor también tiene relación user
    $doctorUserId  = (int) ($order->doctor->user->id ?? 0);

    $isOwner = ($currentUserId === $ownerUserId);
    $isDoctor = ($currentUserId === $doctorUserId);

    if (!$isOwner && !$isDoctor) {
        Log::warning("Intento de acceso no autorizado: Usuario {$currentUserId} intentó ver Orden {$orderId}");
        abort(403, 'No tienes permiso para ver este documento.');
    }

    Log::info("Acceso autorizado. Usuario {$currentUserId} descargando orden {$orderId}");

    return "Descargando PDF seguro para la orden: " . $order->id;
}


// En PublicOrderController.php

public function showSuccess($orderId = null)
{
    // Si no viene ID, mándalo a órdenes y que el usuario vea qué pasó
    if (!$orderId) {
        return redirect()->route('patient.orders')->with('error', 'No se pudo validar el ID de la orden.');
    }

    // Buscamos
    $order = MedicalOrder::with(['paymentTransaction'])->find($orderId);

    // Si el ID existe pero no encuentra la orden, redirige
    if (!$order) {
        return redirect()->route('patient.orders')->with('error', 'Orden no encontrada.');
    }

    // Log::info("DEBUG ACCESS: Intentando acceder a la orden: " . $order->id);
    // Log::info("DEBUG ACCESS: User ID logueado: " . (Auth::check() ? Auth::id() : 'NO LOGUEADO'));
    // Log::info("DEBUG ACCESS: Patient ID del usuario: " . (Auth::check() && Auth::user()->patient ? Auth::user()->patient->id : 'NO TIENE PACIENTE ASOCIADO'));
    // Log::info("DEBUG ACCESS: Patient ID de la orden: " . $order->patient_id);

    // Seguridad simple
    if ($order->patient_id != Auth::user()->patient->id) {
        abort(403);
    }

    return view('payment_success', compact('order'));
}



}
