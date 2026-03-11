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
     */
    public function customOrder()
    {
        $patients = Auth::user()->patients;

        return view('orders.custom', compact('patients'));
    }

    /**
     * Listado de órdenes del paciente logueado.
     */
    public function index()
    {
        // Actualizado: usamos la relación plural y filtramos por titular
        $patient = Auth::user()->patients()->where('relationship', 'self')->first();

        if (!$patient) {
            return redirect()->route('profile.complete');
        }

        $orders = $patient->medicalOrders()->latest()->get();
        return view('patient.orders.index', compact('orders'));
    }

    /**
     * Muestra el formulario de perfil.
     */
    public function completeProfileForm()
    {
        // Actualizado: verificamos si ya existe el perfil 'self'
        if (Auth::user()->patients()->where('relationship', 'self')->exists()) {
            return redirect()->route('home');
        }
        return view('front.complete-profile');
    }

    /**
     * Guarda el perfil y maneja las redirecciones.
     */


    public function storeProfile(Request $request)
{
    $request->validate([
        'full_name'       => 'required|string|min:8',
        'rut'             => 'required|string',
        'birth_date'      => 'required|date',
        'gender_biologic' => 'required|in:M,F',
        'custom_exam_name'=> 'nullable|string'
    ]);

    if ($request->filled('custom_exam_name')) {
        session(['pending_custom_exam' => $request->custom_exam_name]);
    }

    DB::transaction(function () use ($request) {
        $user = Auth::user();
        $user->update(['name' => $request->full_name]);

        $rutLimpio = preg_replace('/[^k0-9]/i', '', $request->rut);

        $user->patients()->updateOrCreate(
            ['relationship' => 'self'],
            [
                'full_name'       => $request->full_name,
                'rut'             => $rutLimpio,
                'birth_date'      => $request->birth_date,
                'gender_biologic' => $request->gender_biologic,
                'prevision'       => 'Particular'
            ]
        );
    });

    // 1. Prioridad: Si venía por una orden personalizada (marcada en el Middleware)
    if (session()->pull('pending_custom_order')) {
        return redirect()->route('orders.custom');
    }

    // 2. Si ya escribió el nombre del examen en el formulario de perfil
    if (session()->has('pending_custom_exam')) {
        return redirect()->route('orders.custom.confirm');
    }

    // 3. Si venía por un Pack estándar
    if (session()->has('pending_exam_id')) {
        $examId = session()->pull('pending_exam_id');
        return redirect()->route('orders.confirm', $examId);
    }

    return redirect()->route('home')->with('success', 'Perfil completado con éxito.');
}






    /**
     * Vista de confirmación para solicitudes manuales ($9.990).
     */
    public function confirmCustomOrder()
    {
        // Actualizado: obtener paciente titular
        $patient = Auth::user()->patients()->where('relationship', 'self')->first();
        $description = session('pending_custom_exam');

        if (!$description) {
            return redirect()->route('orders.custom');
        }

        return view('front.confirm-custom-order', [
            'patient'     => $patient,
            'description' => $description,
            'price'       => 9990
        ]);
    }

    /**
     * Vista de confirmación para Packs estándar.
     */
    public function confirmOrder(ExamType $exam_type)
    {
        // Actualizado: obtener paciente titular
        $patient = Auth::user()->patients()->where('relationship', 'self')->first();

        if (!$patient) {
            session(['pending_exam_id' => $exam_type->id]);
            return redirect()->route('profile.complete');
        }

        return view('front.confirm-order', ['exam' => $exam_type, 'patient' => $patient]);
    }

    /**
     * Crea la orden en la BD y salta a Flow.
     */


    /**
     * Crea la orden en la BD y salta a Flow.
     */
    public function store(Request $request)
    {
        $request->validate([
            'exam_type_id'       => 'required_without:custom_description|exists:exam_types,id',
            'custom_description' => 'required_without:exam_type_id|string',
            'patient_id'         => 'nullable|exists:patients,id' // Validamos que el ID exista si viene
        ]);

        $doctor = Doctor::where('is_active', true)->first();
        if (!$doctor) {
            return back()->with('error', 'Lo sentimos, no hay médicos disponibles en este momento.');
        }

        try {
            $order = DB::transaction(function () use ($request, $doctor) {
                $amount = 9990;
                $examId = null;
                $type   = 'custom';

                if ($request->filled('exam_type_id')) {
                    $exam   = ExamType::findOrFail($request->exam_type_id);
                    $amount = $exam->base_price;
                    $examId = $exam->id;
                    $type   = 'standard';
                }

                /**
                 * LÓGICA DE PACIENTE:
                 * Si viene un patient_id, verificamos que sea del usuario.
                 * Si no viene, usamos el perfil 'self' por defecto.
                 */
                if ($request->filled('patient_id')) {
                    $patient = Auth::user()->patients()->findOrFail($request->patient_id);
                } else {
                    $patient = Auth::user()->patients()->where('relationship', 'self')->firstOrFail();
                }

                return MedicalOrder::create([
                    'id'                 => (string) Str::uuid(),
                    'patient_id'         => $patient->id,
                    'doctor_id'          => $doctor->id,
                    'exam_type_id'       => $examId,
                    'custom_description' => $request->custom_description,
                    'status'             => 'pending',
                    'type'               => $type,
                    'amount'             => $amount,
                    'verification_code'  => strtoupper(Str::random(8)),
                ]);
            });

            session()->forget(['pending_custom_exam', 'pending_exam_id']);
            return $this->processFlowPayment($order);

        } catch (\Exception $e) {
            Log::error("Error al crear orden: " . $e->getMessage());
            return back()->with('error', 'No pudimos procesar tu orden: ' . $e->getMessage());
        }
    }



    /**
     * Reintenta el pago de una orden pendiente.
     */
    public function retryPayment(MedicalOrder $order)
    {
        $patient = Auth::user()->patients()->where('relationship', 'self')->first();

        if (!$patient || $order->patient_id !== $patient->id || $order->status !== 'pending') {
            return redirect()->route('patient.orders')->with('error', 'Esta orden no puede ser procesada.');
        }

        return $this->processFlowPayment($order);
    }

    private function processFlowPayment(MedicalOrder $order)
    {
        try {
            $flowResponse = $this->flow->createPayment($order);

            if ($flowResponse && isset($flowResponse->token)) {
                $url = $flowResponse->url . "?token=" . $flowResponse->token;
                return redirect()->away($url);
            }

            Log::error("Flow sin token para orden: {$order->id}", ['response' => $flowResponse]);
            return redirect()->route('patient.orders')
                ->with('warning', 'La orden se guardó pero la pasarela de pago no respondió. Intenta pagar desde tu panel.');

        } catch (\Exception $e) {
            Log::error("Excepción en Flow: " . $e->getMessage());
            return redirect()->route('patient.orders')->with('error', 'Error al conectar con la pasarela de pago.');
        }
    }

    public function showSuccess($orderId = null)
    {
        if (!$orderId) {
            return redirect()->route('patient.orders')->with('error', 'No se encontró la referencia de la orden.');
        }

        $order = MedicalOrder::with(['paymentTransaction'])->find($orderId);
        $patient = Auth::user()->patients()->where('relationship', 'self')->first();

        if (!$order || !$patient || $order->patient_id != $patient->id) {
            return redirect()->route('patient.orders')->with('error', 'Orden no encontrada o acceso no autorizado.');
        }

        return view('payment_success', compact('order'));
    }

    public function download($orderId)
    {
        $order = MedicalOrder::with(['patient.user', 'doctor.user'])->findOrFail($orderId);

        $currentUserId = (int) auth()->id();
        $ownerUserId   = (int) ($order->patient->user->id ?? 0);
        $doctorUserId  = (int) ($order->doctor->user->id ?? 0);

        if ($currentUserId !== $ownerUserId && $currentUserId !== $doctorUserId) {
            abort(403, 'No tienes permiso para descargar este documento.');
        }

        return "Iniciando descarga segura de la orden: " . $order->id;
    }
}
