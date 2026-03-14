<?php

namespace App\Http\Controllers;

use App\Models\MedicalOrder;
use App\Models\ExamType;
use App\Models\GatewayTransaction;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\FlowService;

class MedicalOrderController extends Controller
{
    /**
     * Listado de órdenes: Inteligente según el Rol con liberación de bloqueos.
     */



    public function index()
    {
        $user = Auth::user();

        // 1. Garbage Collector: Liberamos órdenes cuyo "claimed_at" expiró (20 minutos)
        // Nota: Asegúrate de que tu tabla 'orders' tenga 'doctor_id' y 'claimed_at'
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
        }, 'doctor.user', 'examType', 'interactions']); // Agregamos 'interactions' para ver si hay chat

        // 3. Lógica de filtrado para Doctores
        if ($user->hasRole('doctor')) {
            $doctor = $user->doctor;

            $query->where(function($q) use ($doctor) {
                // Órdenes ya tomadas por este doctor
                $q->where('doctor_id', $doctor->id)
                  ->whereIn('status', ['paid', 'pending'])

                // O órdenes pagadas sin doctor de su especialidad
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
                       ->where('type', 'custom') // <--- Usamos la nueva columna 'type'
                       ->where('status', 'paid');
                });
            });
        }

        $orders = $query->latest()->paginate(10);

        return view('admin.orders.index', compact('orders'));
    }



    /**
     * Muestra el formulario de firma.
     * Cambiado $order por $medical_order para coincidir con la ruta.
     */
    public function showSignForm(Order $order) // <--- Cambiado de MedicalOrder a Order
    {
        // Laravel ahora busca automáticamente por el ID (UUID) en la tabla 'orders'
        $order->load(['patient', 'doctor.user', 'examType', 'interactions']);

        $user = Auth::user();
        $doctor = $user->doctor;

        // Validación de bloqueo (Claimed)
        if ($order->doctor_id && $order->doctor_id !== $doctor->id && $order->claimed_at && $order->claimed_at->gt(now()->subMinutes(20))) {
            return redirect()->route('admin.orders.index')
                            ->with('error', 'Esta orden está siendo revisada por otro profesional.');
        }

        // Marcamos la orden como tomada por este médico
        $order->update([
            'doctor_id' => $doctor->id,
            'claimed_at' => now()
        ]);

        Log::info("Médico ID: {$doctor->id} ha tomado la orden {$order->id} para revisión.");

        return view('admin.orders.sign', compact('order'));
    }

    /**
     * Procesa la firma digital.
     * Cambiado $order por $medical_order.
     */
    public function processSignature(Request $request, MedicalOrder $medical_order)
    {
        $order = $medical_order;
        $request->validate([
            'clinical_context' => 'required|string|min:10'
        ]);

        $doctor = Auth::user()->doctor;

        if (strval($order->doctor_id) !== strval($doctor->id)) {
            return redirect()->route('admin.orders.index')->with('error', 'Sesión expirada o permiso denegado.');
        }

        try {
            DB::transaction(function () use ($order, $doctor, $request) {
                $order->update([
                    'status'           => 'signed',
                    'signed_at'        => now(),
                    'clinical_context' => $request->clinical_context,
                ]);

                Transaction::where('reference_id', $order->id)
                    ->update(['receiver_id' => $doctor->user_id]);
            });

            return redirect()->route('admin.doctor.panel')->with('success', 'Orden generada y firmada exitosamente.');

        } catch (\Exception $e) {
            Log::error("Error firma: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al procesar la firma.');
        }
    }

    /**
     * Rechazar orden e iniciar reembolso.
     * Nota: Aquí usamos $medical_order como ID para el findOrFail manual.
     */


public function rejectOrder(Request $request, Order $order, \App\Services\FlowService $flowService)
{
    Log::info('--- INICIO PROCESO RECHAZO MANUAL ---', [
        'user_id' => auth()->id(),
        'order_id' => $order->id
    ]);

    try {
        $user = auth()->user();
        $doctor = $user->doctor;

        // 1. Validación de permisos: El médico que la tiene tomada o un Admin
        if (!$doctor || ($order->doctor_id !== $doctor->id && !$user->hasRole('admin'))) {
            Log::error("CRASH RECHAZO: No tienes permiso sobre esta orden.", [
                'order_doc' => $order->doctor_id,
                'auth_doc' => $doctor->id ?? 'null'
            ]);
            return abort(403, 'No tienes permiso sobre esta orden.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        return DB::transaction(function () use ($request, $order, $flowService) {

            // 2. Actualizamos el estado de la orden localmente
            $order->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'claimed_at' => null, // Liberamos el bloqueo temporal
            ]);

            Log::info("Orden {$order->id} marcada como RECHAZADA. Motivo: {$request->rejection_reason}");

            /* --- BLOQUE FLOW (COMENTADO TEMPORALMENTE) ---

            $gatewayTrx = GatewayTransaction::where('payable_id', $order->id)
                ->whereIn('status', ['authorized', 'completed'])
                ->first();

            if ($gatewayTrx) {
                $rawResponse = json_decode($gatewayTrx->raw_response);
                $flowTrxId = $rawResponse->flowOrder ?? null;

                // Llamamos al servicio de reembolso
                $refundResult = $flowService->requestRefund($order, $gatewayTrx, $flowTrxId);

                if ($refundResult) {
                    return redirect()->route('admin.doctor.panel')->with('info', 'Orden rechazada y reembolso solicitado.');
                } else {
                    return redirect()->route('admin.doctor.panel')->with('error', 'Orden rechazada, pero falló el reembolso automático.');
                }
            }
            */

            return redirect()->route('admin.doctor.panel')->with('warning', 'Orden rechazada. (Lógica de reembolso pendiente de activación).');
        });

    } catch (\Exception $e) {
        Log::error("CRASH RECHAZO (Excepción): " . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        return redirect()->back()->with('error', 'Error interno al procesar el rechazo.');
    }
}


public function derivateOrder(Request $request, MedicalOrder $medical_order)
{
    $request->validate([
        'specialty_id' => 'required|exists:specialties,id'
    ]);

    $order = $medical_order;
    $myDoctorId = auth()->user()->doctor->id ?? null;

    // Solo el médico que tiene "tomada" la orden puede derivarla
    if ($order->doctor_id == $myDoctorId) {
        $order->update([
            'doctor_id' => null,
            'claimed_at' => null,
            // Aquí podrías tener un campo 'target_specialty_id' en tu tabla orders
            // O si usas el exam_type_id para filtrar, podrías dejarlo nulo
            // y manejar un flag de "abierto a todos".
            // Por ahora, lo liberamos para que cualquier médico lo vea.
        ]);

        return redirect()->route('admin.doctor.panel')->with('info', 'Orden derivada exitosamente.');
    }

    return redirect()->back()->with('error', 'No tienes permiso para realizar esta acción.');
}


/**
 * Libera una orden bloqueada por un médico para que otros puedan tomarla.
 */
public function releaseOrder(Request $request, Order $order)
{
    $doctor = auth()->user()->doctor;
    $myDoctorId = $doctor->id ?? null;

    // Verificamos que quien libera sea el mismo que la tiene tomada
    // Opcionalmente podrías permitir que un admin la libere también
    if ($order->doctor_id === $myDoctorId || auth()->user()->hasRole('admin')) {

        $order->update([
            'doctor_id' => null,
            'claimed_at' => null
        ]);

        Log::info("Orden {$order->id} liberada por el médico ID: {$myDoctorId}");

        // Redirección dinámica basada en el origen del clic
        if ($request->redirect_to === 'index') {
            return redirect()->route('admin.doctor.panel')->with('success', 'Orden liberada y devuelta al listado.');
        }

        return redirect()->route('admin.doctor.panel')->with('success', 'Has liberado la orden correctamente.');
    }

    return redirect()->route('admin.doctor.panel')->with('error', 'No tienes permisos para liberar esta orden.');
}






    public function create()
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor) return redirect()->back()->with('error', 'Perfil médico no encontrado.');

        $exams = ExamType::where('specialty_id', $doctor->specialty_id)
            ->orWhereHas('specialty', fn($q) => $q->where('name', 'LIKE', '%General%'))
            ->where('is_active', true)
            ->get();

        return view('admin.orders.create', compact('exams'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'exam_type_id' => 'required|exists:exam_types,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $doctor = Auth::user()->doctor;

        MedicalOrder::create([
            'patient_id' => $request->patient_id,
            'doctor_id' => $doctor->id,
            'exam_type_id' => $request->exam_type_id,
            'amount' => $request->amount,
            'status' => 'pending',
            'claimed_at' => now(),
            'verification_code' => MedicalOrder::generateUniqueVerificationCode(),
        ]);

        return redirect()->route('admin.orders.index')->with('success', 'Orden médica generada.');
    }
}
