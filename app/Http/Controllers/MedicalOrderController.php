<?php

namespace App\Http\Controllers;

use App\Models\MedicalOrder;
use App\Models\ExamType;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MedicalOrderController extends Controller
{
    /**
     * Listado de órdenes: Inteligente según el Rol con liberación de bloqueos.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Garbage Collector: Liberamos órdenes cuyo "claimed_at" expiró (20 minutos)
        // Ahora incluimos 'paid' porque es el estado en el que los médicos trabajan.
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
                // 1. Ver órdenes que ya tengo tomadas YO (independiente del estado)
                $q->where('doctor_id', $doctor->id)
                  ->whereIn('status', ['paid', 'pending'])

                // 2. O ver órdenes de mi especialidad PAGADAS y sin asignar
                ->orWhere(function($sq) use ($doctor) {
                    $sq->whereNull('doctor_id')
                       ->where('status', 'paid') // Solo mostramos lo que ya está pagado
                       ->whereHas('examType', function($eq) use ($doctor) {
                           $eq->where('specialty_id', $doctor->specialty_id);
                       });
                })
                // 3. O ver solicitudes especiales (custom) PAGADAS y sin asignar
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
     * Muestra el formulario de firma y MARCA la orden como reclamada (claimed_at).
     */
    public function showSignForm(MedicalOrder $order)
    {
        $order->load(['patient' => fn($q) => $q->withTrashed(), 'examType']);
        $user = Auth::user();
        $doctor = $user->doctor;

        // VALIDACIÓN DE BLOQUEO ACTIVO
        if ($order->doctor_id && $order->doctor_id !== $doctor->id && $order->claimed_at > now()->subMinutes(20)) {
            return redirect()->route('admin.orders.index')
                             ->with('error', 'Esta orden está siendo revisada por otro profesional.');
        }

        // MARCAR COMO TOMADA
        $order->update([
            'doctor_id' => $doctor->id,
            'claimed_at' => now()
        ]);

        Log::info("Médico ID: {$doctor->id} ha tomado la orden {$order->id} para revisión.");

        $doctor->load('specialty');
        return view('admin.orders.sign', compact('order'));
    }

    /**
     * Procesa la firma digital definitiva y el cobro.
     */
    public function processSignature(Request $request, MedicalOrder $order)
    {
        $doctor = Auth::user()->doctor;

        if ($order->doctor_id !== $doctor->id) {
            return redirect()->route('admin.orders.index')
                             ->with('error', 'El tiempo de reserva de esta orden expiró.');
        }

        DB::transaction(function () use ($order, $doctor) {
            // 1. Actualizar Orden a Firmada
            $order->update([
                'status'    => 'signed',
                'signed_at' => now(),
            ]);

            // 2. Vincular Transacción al Médico para su pago/comisión
            Transaction::where('reference_id', $order->id)
                ->update([
                    'receiver_id' => $doctor->user_id
                ]);
        });

        Log::info("Orden {$order->id} firmada exitosamente por Dr. {$doctor->id}");

        return redirect()->route('admin.orders.index')
                        ->with('success', 'Orden firmada digitalmente. El pago ha sido abonado a tu cuenta.');
    }

    /**
     * Nuevo método: Rechazar orden (especialmente útil para Custom Orders)
     */
    public function rejectOrder(Request $request, MedicalOrder $order)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $doctor = Auth::user()->doctor;

        if ($order->doctor_id !== $doctor->id) {
            return redirect()->route('admin.orders.index')->with('error', 'No tienes permiso sobre esta orden.');
        }

        $order->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            // Al rechazarla, la orden deja de estar "claimed" pero mantiene quién la rechazó
            'claimed_at' => null
        ]);

        Log::warning("Orden {$order->id} rechazada por Dr. {$doctor->id}. Motivo: {$request->rejection_reason}");

        return redirect()->route('admin.orders.index')->with('info', 'La orden ha sido rechazada y marcada para revisión administrativa.');
    }

    /**
     * Permite al médico liberar la orden manualmente si decide no firmarla (sin rechazarla).
     */
    public function releaseOrder(MedicalOrder $order)
    {
        $doctor = Auth::user()->doctor;

        if ($order->doctor_id === $doctor->id && in_array($order->status, ['pending', 'paid'])) {
            $order->update([
                'doctor_id' => null,
                'claimed_at' => null
            ]);
            return redirect()->route('admin.orders.index')->with('info', 'Has liberado la orden.');
        }

        return redirect()->route('admin.orders.index');
    }

    // ... create y store se mantienen igual ...










    /**
     * Emisión manual de órdenes por parte del médico.
     */
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
            'claimed_at' => now(), // Al crearla él mismo, ya queda "tomada"
            'verification_code' => MedicalOrder::generateUniqueVerificationCode(),
        ]);

        return redirect()->route('admin.orders.index')->with('success', 'Orden médica generada y asignada correctamente.');
    }
}
