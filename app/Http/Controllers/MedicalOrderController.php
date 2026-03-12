<?php

namespace App\Http\Controllers;

use App\Models\MedicalOrder;
use App\Models\ExamType;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MedicalOrderController extends Controller
{
    /**
     * Listado de órdenes: Inteligente según el Rol.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Si es doctor, liberamos órdenes que quedaron en el "limbo" (abiertas pero no firmadas)
        if ($user->hasRole('doctor')) {
            MedicalOrder::where('status', 'pending')
                ->whereNotNull('doctor_id')
                ->where('updated_at', '<', now()->subMinutes(20))
                ->update(['doctor_id' => null]);
        }

        $query = MedicalOrder::with(['patient' => function($q) {
            $q->withTrashed();
        }, 'doctor.user', 'examType']);

        if ($user->hasRole('doctor')) {
            $doctor = $user->doctor;

            $query->where(function($q) use ($doctor) {
                // Ver lo que es mío
                $q->where('doctor_id', $doctor->id)
                // O ver lo pendiente de mi especialidad
                ->orWhere(function($sq) use ($doctor) {
                    $sq->whereNull('doctor_id')
                       ->where('status', 'pending')
                       ->whereHas('examType', function($eq) use ($doctor) {
                           $eq->where('specialty_id', $doctor->specialty_id);
                       });
                })
                // O ver solicitudes especiales (custom) sin asignar
                ->orWhere(function($sq) {
                    $sq->whereNull('doctor_id')
                       ->whereNull('exam_type_id')
                       ->where('status', 'pending');
                });
            });
        }

        $orders = $query->latest()->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Muestra el formulario de firma y BLOQUEA la orden para el médico actual.
     */
    public function showSignForm(MedicalOrder $order)
    {
        $order->load(['patient' => fn($q) => $q->withTrashed(), 'examType']);
        $user = Auth::user();
        $doctor = $user->doctor;

        // LÓGICA DE BLOQUEO:
        // Si no tiene doctor, este médico la toma para que nadie más la vea en el index
        if (!$order->doctor_id) {
            $order->update(['doctor_id' => $doctor->id]);
            Log::info("Médico ID: {$doctor->id} ha bloqueado la orden {$order->id} para revisión.");
        }
        // Si ya tiene doctor y no soy yo, prohibir acceso
        elseif ($order->doctor_id !== $doctor->id) {
            return redirect()->route('admin.orders.index')
                             ->with('error', 'Esta orden ya está siendo revisada por otro profesional.');
        }

        $doctor->load('specialty');
        return view('admin.orders.sign', compact('order'));
    }

    /**
     * Procesa la firma digital y vincula la transacción financiera.
     */
    public function processSignature(Request $request, MedicalOrder $order)
    {
        $doctor = Auth::user()->doctor;

        if ($order->doctor_id && $order->doctor_id !== $doctor->id) {
            abort(403, 'Esta orden ya fue tomada por otro profesional.');
        }

        // Usamos una transacción de BD para asegurar que firma y pago se vinculen sí o sí
        \DB::transaction(function () use ($order, $doctor) {
            // 1. Marcar como firmada
            $order->update([
                'doctor_id' => $doctor->id,
                'status'    => 'signed',
                'signed_at' => now(),
            ]);

            // 2. RECLAMAR TRANSACCIÓN: Buscamos la transacción del webhook que quedó con receiver_id null
            Transaction::where('reference_id', $order->id)
                ->whereNull('receiver_id')
                ->update([
                    'receiver_id' => $doctor->user_id
                ]);
        });

        // Aquí dispararías el Job del PDF si fuera necesario
        Log::info("Orden {$order->id} firmada y transacción asignada al médico {$doctor->id}");

        return redirect()->route('admin.orders.index') // O admin.doctor.panel si lo prefieres
                        ->with('success', 'Orden firmada digitalmente con éxito y pago asignado.');
    }

    /**
     * (Opcional) Permite al médico liberar una orden si decide no firmarla.
     */
    public function releaseOrder(MedicalOrder $order)
    {
        $doctor = Auth::user()->doctor;

        if ($order->doctor_id === $doctor->id && $order->status !== 'signed') {
            $order->update(['doctor_id' => null]);
            return redirect()->route('admin.orders.index')->with('info', 'Has liberado la orden para otros profesionales.');
        }

        return redirect()->route('admin.orders.index');
    }

    public function create()
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor) return redirect()->back()->with('error', 'No tienes un perfil de doctor asociado.');

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
            'verification_code' => MedicalOrder::generateUniqueVerificationCode(),
        ]);

        return redirect()->route('admin.orders.index')->with('success', 'Orden médica generada correctamente.');
    }
}
