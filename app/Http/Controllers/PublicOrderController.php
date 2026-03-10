<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\ExamType;
use App\Models\MedicalOrder;
use App\Models\Doctor;
use App\Services\FlowService;
use App\Helpers\RutHelper;
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
            'rut'             => 'required|string', // Se limpia dentro de la transacción
            'birth_date'      => 'required|date',
            'gender_biologic' => 'required|in:M,F',
        ]);

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

        if (session()->has('pending_exam_id')) {
            $examId = session()->pull('pending_exam_id');
            return redirect()->route('orders.confirm', $examId);
        }

        return redirect()->route('home')->with('success', 'Perfil completado.');
    }

    /**
     * Vista previa antes de pagar (Checkout Step)
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
        $request->validate([
            'exam_type_id' => 'required|exists:exam_types,id'
        ]);

        $exam = ExamType::findOrFail($request->exam_type_id);
        $doctor = Doctor::where('is_active', true)->first();

        if (!$doctor) {
            return back()->with('error', 'No hay médicos disponibles para la firma en este momento.');
        }

        try {
            // 1. Creamos la orden en DB
                $order = DB::transaction(function () use ($request, $doctor, $exam) {
                    return MedicalOrder::create([
                        'id'                => (string) Str::uuid(),
                        'patient_id'        => Auth::user()->patient->id,
                        'doctor_id'         => $doctor->id,
                        'exam_type_id'      => $exam->id,
                        'status'            => 'pending',
                        'type'              => 'standard', // <--- AGREGAR ESTO
                        'amount'            => $exam->base_price,
                        'verification_code' => strtoupper(Str::random(8)),
                    ]);
                });

            // 2. Iniciamos el proceso en Flow
            return $this->processFlowPayment($order);

        } catch (\Exception $e) {
            Log::error("Error creando orden: " . $e->getMessage());
            return back()->with('error', 'Hubo un error al procesar tu solicitud.');
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

public function showSuccess($orderId)
{
    // Usamos el nombre exacto de tu relación: paymentTransaction
    $order = MedicalOrder::with(['paymentTransaction'])
                ->findOrFail($orderId);

    // Validamos que el usuario logueado sea el dueño de la orden
    if ($order->patient_id !== Auth::user()->patient->id) {
        abort(403, 'No tienes acceso a esta orden.');
    }

    return view('payment_success', compact('order'));
}



}
