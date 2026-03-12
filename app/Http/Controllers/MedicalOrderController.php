<?php

namespace App\Http\Controllers;

use App\Models\MedicalOrder;
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
     * Listado de órdenes: Inteligente según el Rol con liberación de bloqueos.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Garbage Collector: Liberamos órdenes cuyo "claimed_at" expiró (20 minutos)
        MedicalOrder::whereIn('status', ['pending', 'paid'])
            ->whereNotNull('claimed_at')
            ->where('claimed_at', '<', now()->subMinutes(20))
            ->update([
                'doctor_id' => null,
                'claimed_at' => null
            ]);

        $query = MedicalOrder::with(['patient' => function($q) {
            $q->withTrashed();
        }, 'doctor.user', 'examType']);

        if ($user->hasRole('doctor')) {
            $doctor = $user->doctor;

            $query->where(function($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id)
                  ->whereIn('status', ['paid', 'pending'])
                ->orWhere(function($sq) use ($doctor) {
                    $sq->whereNull('doctor_id')
                       ->where('status', 'paid')
                       ->whereHas('examType', function($eq) use ($doctor) {
                           $eq->where('specialty_id', $doctor->specialty_id);
                       });
                })
                ->orWhere(function($sq) {
                    $sq->whereNull('doctor_id')
                       ->whereNull('exam_type_id')
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
    public function showSignForm(MedicalOrder $medical_order)
    {
        $order = $medical_order; // Mantenemos la variable interna por compatibilidad
        $order->load(['patient', 'doctor.specialties', 'examType']);
        $user = Auth::user();
        $doctor = $user->doctor;

        if ($order->doctor_id && $order->doctor_id !== $doctor->id && $order->claimed_at > now()->subMinutes(20)) {
            return redirect()->route('admin.orders.index')
                             ->with('error', 'Esta orden está siendo revisada por otro profesional.');
        }

        $order->update([
            'doctor_id' => $doctor->id,
            'claimed_at' => now()
        ]);

        Log::info("Médico ID: {$doctor->id} ha tomado la orden {$order->id} para revisión.");

        $doctor->load('specialties');
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
    public function rejectOrder(Request $request, $medical_order, FlowService $flowService)
    {
        Log::info('--- INICIO PROCESO RECHAZO ---', [
            'user_id' => auth()->id(),
            'order_id' => $medical_order
        ]);

        try {
            $order = MedicalOrder::findOrFail($medical_order);
            $doctor = auth()->user()->doctor;

            if (!$doctor || $order->doctor_id !== $doctor->id) {
                return abort(403, 'No tienes permiso sobre esta orden.');
            }

            $request->validate(['rejection_reason' => 'required|string|max:500']);

            return DB::transaction(function () use ($request, $order, $flowService) {
                $order->update([
                    'status' => 'rejected',
                    'rejection_reason' => $request->rejection_reason,
                    'claimed_at' => null
                ]);

                $gatewayTrx = GatewayTransaction::where('payable_id', $order->id)
                    ->where('status', 'authorized')
                    ->first();

                if ($gatewayTrx) {
                    $refundResult = $flowService->requestRefund($order, $gatewayTrx, $gatewayTrx->token);
                    if ($refundResult) {
                        return redirect()->route('admin.doctor.panel')->with('info', 'Orden rechazada y reembolso solicitado.');
                    }
                }

                return redirect()->route('admin.doctor.panel')->with('warning', 'Orden rechazada (reembolso manual requerido).');
            });

        } catch (\Exception $e) {
            Log::error("CRASH RECHAZO: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error interno al procesar.');
        }
    }

    /**
     * Libera la orden.
     * Cambiado $order por $medical_order.
     */
    public function releaseOrder(MedicalOrder $medical_order)
    {
        $order = $medical_order;
        $myDoctorId = auth()->user()->doctor->id ?? null;

        if ($order->doctor_id == $myDoctorId) {
            $order->doctor_id = null;
            $order->claimed_at = null;
            $order->save();

            return redirect()->route('admin.doctor.panel')->with('success', 'La orden ha sido liberada.');
        }

        return redirect()->route('admin.doctor.panel')->with('error', 'No tienes permiso.');
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
