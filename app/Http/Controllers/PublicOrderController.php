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
     * Vinculado a la ruta: orders.custom
     */
    public function customOrder()
    {
        return view('orders.custom');
    }

    /**
     * Listado de órdenes del paciente logueado.
     */
    public function index()
    {
        $patient = Auth::user()->patient;

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
        if (Auth::user()->patient) {
            return redirect()->route('home');
        }
        return view('front.complete-profile');
    }

    /**
     * Guarda el perfil y maneja las redirecciones según lo que el usuario quería hacer.
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

        // Si el usuario viene desde el formulario de "No encontré mi examen"
        if ($request->filled('custom_exam_name')) {
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

        // Redirección inteligente post-perfil
        if (session()->has('pending_custom_exam')) {
            return redirect()->route('orders.custom.confirm');
        }

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
        $patient = Auth::user()->patient;
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
        $patient = Auth::user()->patient;

        if (!$patient) {
            session(['pending_exam_id' => $exam_type->id]);
            return redirect()->route('profile.complete');
        }

        return view('front.confirm-order', ['exam' => $exam_type, 'patient' => $patient]);
    }

    /**
     * Crea la orden en la BD y salta a Flow.
     */
    public function store(Request $request)
    {
        $request->validate([
            'exam_type_id'       => 'required_without:custom_description|exists:exam_types,id',
            'custom_description' => 'required_without:exam_type_id|string'
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

                return MedicalOrder::create([
                    'id'                 => (string) Str::uuid(),
                    'patient_id'         => Auth::user()->patient->id,
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
            return back()->with('error', 'No pudimos procesar tu orden. Inténtalo de nuevo.');
        }
    }

    /**
     * Reintenta el pago de una orden pendiente desde el panel del paciente.
     */
    public function retryPayment(MedicalOrder $order)
    {
        if ($order->patient_id !== Auth::user()->patient->id || $order->status !== 'pending') {
            return redirect()->route('patient.orders')->with('error', 'Esta orden no puede ser procesada.');
        }

        return $this->processFlowPayment($order);
    }

    /**
     * Lógica centralizada para obtener el token de Flow y redirigir.
     */
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

    /**
     * Muestra la pantalla de éxito tras el pago.
     */
    public function showSuccess($orderId = null)
    {
        if (!$orderId) {
            return redirect()->route('patient.orders')->with('error', 'No se encontró la referencia de la orden.');
        }

        $order = MedicalOrder::with(['paymentTransaction'])->find($orderId);

        if (!$order || $order->patient_id != Auth::user()->patient->id) {
            return redirect()->route('patient.orders')->with('error', 'Orden no encontrada o acceso no autorizado.');
        }

        return view('payment_success', compact('order'));
    }

    /**
     * Generación de PDF (Segura).
     */
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
