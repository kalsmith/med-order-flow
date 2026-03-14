<?php

namespace App\Http\Controllers;

use App\Models\Order; // Usamos el modelo unificado
use App\Models\ExamType;
use App\Models\GatewayTransaction;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\FlowService;

class MedicalOrderController extends Controller
{
    /**
     * Listado de órdenes: Inteligente según el Rol con liberación de bloqueos expirados.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Garbage Collector: Liberamos órdenes cuyo "claimed_at" expiró (20 minutos)
        Order::whereIn('status', ['pending', 'paid'])
            ->whereNotNull('claimed_at')
            ->where('claimed_at', '<', now()->subMinutes(20))
            ->update([
                'doctor_id' => null,
                'claimed_at' => null
            ]);

        // 2. Query Base con Eager Loading
        $query = Order::with(['patient' => function($q) {
            $q->withTrashed();
        }, 'doctor.user', 'examType', 'interactions']);

        // 3. Lógica de filtrado para Doctores
        if ($user->hasRole('doctor')) {
            $doctor = $user->doctor;

            $query->where(function($q) use ($doctor) {
                // Órdenes ya tomadas por este doctor
                $q->where('doctor_id', $doctor->id)
                  ->whereIn('status', ['paid', 'pending'])

                // O órdenes pagadas de su especialidad sin doctor asignado
                ->orWhere(function($sq) use ($doctor) {
                    $sq->whereNull('doctor_id')
                       ->where('status', 'paid')
                       ->whereHas('examType', function($eq) use ($doctor) {
                           $eq->where('specialty_id', $doctor->specialty_id);
                       });
                })

                // O solicitudes especiales (custom) pagadas sin doctor asignado
                ->orWhere(function($sq) {
                    $sq->whereNull('doctor_id')
                       ->where('type', 'custom')
                       ->where('status', 'paid');
                });
            });
        }

        $orders = $query->latest()->paginate(10);

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Muestra el formulario de firma y bloquea la orden para el médico.
     */
public function showSignForm(Order $order)
{
    $order->load(['patient', 'doctor.user', 'examType', 'interactions']);

    $user = Auth::user();
    $doctor = $user->doctor;

    // VALIDACIÓN DE BLOQUEO CORREGIDA
    // Bloqueamos SOLO SI:
    // 1. Tiene un doctor asignado ($order->doctor_id)
    // 2. ESE doctor NO es el médico actual ($order->doctor_id !== $doctor->id)
    // 3. El bloqueo aún no ha expirado (claimed_at > 20 min atrás)
    if (
        $order->doctor_id &&
        $order->doctor_id !== $doctor->id &&
        $order->claimed_at &&
        $order->claimed_at->gt(now()->subMinutes(20))
    ) {
        return redirect()->route('admin.doctor.panel')
                         ->with('error', 'Esta orden está siendo revisada por otro profesional.');
    }

    // Si llegamos aquí, es porque la orden está libre O es nuestra.
    // Actualizamos el timestamp para renovar los 20 minutos de "reserva".
    $order->update([
        'doctor_id' => $doctor->id,
        'claimed_at' => now()
    ]);

    Log::info("Médico ID: {$doctor->id} renovó/tomó la orden {$order->id} para revisión.");

    return view('admin.orders.sign', compact('order'));
}

    /**
     * Procesa la firma digital y finaliza el ciclo médico.
     */
    public function processSignature(Request $request, Order $order)
    {
        $request->validate([
            'clinical_context' => 'required|string|min:10'
        ]);

        $doctor = Auth::user()->doctor;

        // Verificación de seguridad: que el médico sea el dueño del bloqueo
        if ($order->doctor_id !== $doctor->id) {
            return redirect()->route('admin.doctor.panel')->with('error', 'No tienes permiso para firmar esta orden o el bloqueo expiró.');
        }

        try {
            DB::transaction(function () use ($order, $doctor, $request) {
                $order->update([
                    'status'           => 'signed',
                    'signed_at'        => now(),
                    'clinical_context' => $request->clinical_context,
                    'claimed_at'       => null // Liberamos el bloqueo al finalizar
                ]);

                // Vinculamos la transacción al médico receptor para pagos/comisiones
                Transaction::where('reference_id', $order->id)
                    ->update(['receiver_id' => $doctor->user_id]);
            });

            return redirect()->route('admin.doctor.panel')->with('success', 'Orden firmada exitosamente. El paciente recibirá su documento.');

        } catch (\Exception $e) {
            Log::error("Error en processSignature: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al procesar la firma electrónica.');
        }
    }

    /**
     * Rechazar orden e iniciar lógica de reembolso (Flow).
     */
    public function rejectOrder(Request $request, Order $order, FlowService $flowService)
    {
        Log::info('--- INICIO PROCESO RECHAZO MANUAL ---', [
            'user_id' => auth()->id(),
            'order_id' => $order->id
        ]);

        try {
            $user = auth()->user();
            $doctor = $user->doctor;

            if (!$doctor || ($order->doctor_id !== $doctor->id && !$user->hasRole('admin'))) {
                return abort(403, 'No tienes permiso sobre esta orden.');
            }

            $request->validate(['rejection_reason' => 'required|string|max:500']);

            return DB::transaction(function () use ($request, $order, $flowService) {
                $order->update([
                    'status' => 'rejected',
                    'rejection_reason' => $request->rejection_reason,
                    'claimed_at' => null,
                ]);

                Log::info("Orden {$order->id} marcada como RECHAZADA.");

                /* --- Lógica de Reembolso Flow (Comentada) ---
                $gatewayTrx = GatewayTransaction::where('payable_id', $order->id)
                    ->whereIn('status', ['authorized', 'completed'])
                    ->first();

                if ($gatewayTrx) {
                    $rawResponse = json_decode($gatewayTrx->raw_response);
                    $flowTrxId = $rawResponse->flowOrder ?? null;
                    $flowService->requestRefund($order, $gatewayTrx, $flowTrxId);
                }
                */

                return redirect()->route('admin.doctor.panel')->with('warning', 'Orden rechazada. Reembolso pendiente de procesamiento manual.');
            });

        } catch (\Exception $e) {
            Log::error("Error en rejectOrder: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al procesar el rechazo.');
        }
    }

    /**
     * Deriva la orden (la libera para que otro profesional de la especialidad la tome).
     */
    public function derivateOrder(Request $request, Order $order)
    {
        $request->validate([
            'specialty_id' => 'required|exists:specialties,id'
        ]);

        $doctor = auth()->user()->doctor;

        if ($order->doctor_id === $doctor->id) {
            $order->update([
                'doctor_id' => null,
                'claimed_at' => null,
                // Opcional: actualizar specialty_id si el flujo lo requiere
            ]);

            return redirect()->route('admin.doctor.panel')->with('info', 'Orden derivada exitosamente.');
        }

        return redirect()->back()->with('error', 'No tienes permiso para derivar esta orden.');
    }

    /**
     * Libera manualmente una orden bloqueada.
     */
    public function releaseOrder(Request $request, Order $order)
    {
        $user = auth()->user();
        $doctorId = $user->doctor->id ?? null;

        if ($order->doctor_id === $doctorId || $user->hasRole('admin')) {
            $order->update([
                'doctor_id' => null,
                'claimed_at' => null
            ]);

            Log::info("Orden {$order->id} liberada manualmente.");

            return redirect()->route('admin.doctor.panel')->with('success', 'Orden liberada correctamente.');
        }

        return redirect()->route('admin.doctor.panel')->with('error', 'No tienes permisos para liberar esta orden.');
    }
}




    // public function create()
    // {
    //     $doctor = Auth::user()->doctor;
    //     if (!$doctor) return redirect()->back()->with('error', 'Perfil médico no encontrado.');

    //     $exams = ExamType::where('specialty_id', $doctor->specialty_id)
    //         ->orWhereHas('specialty', fn($q) => $q->where('name', 'LIKE', '%General%'))
    //         ->where('is_active', true)
    //         ->get();

    //     return view('admin.orders.create', compact('exams'));
    // }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'patient_id' => 'required|exists:patients,id',
    //         'exam_type_id' => 'required|exists:exam_types,id',
    //         'amount' => 'required|numeric|min:0',
    //     ]);

    //     $doctor = Auth::user()->doctor;

    //     MedicalOrder::create([
    //         'patient_id' => $request->patient_id,
    //         'doctor_id' => $doctor->id,
    //         'exam_type_id' => $request->exam_type_id,
    //         'amount' => $request->amount,
    //         'status' => 'pending',
    //         'claimed_at' => now(),
    //         'verification_code' => MedicalOrder::generateUniqueVerificationCode(),
    //     ]);

    //     return redirect()->route('admin.orders.index')->with('success', 'Orden médica generada.');
    // }
