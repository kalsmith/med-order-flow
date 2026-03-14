<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
     * Listado de órdenes: Inteligente según el Rol.
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
                // Órdenes ya tomadas por este doctor (ID de tabla doctors)
                $q->where('doctor_id', $doctor->id)
                  ->whereIn('status', ['paid', 'pending', 'signed'])

                // O órdenes pagadas de su especialidad sin doctor asignado
                ->orWhere(function($sq) use ($doctor) {
                    $sq->whereNull('doctor_id')
                       ->where('status', 'paid')
                       ->whereHas('examType', function($eq) use ($doctor) {
                           // Asumiendo que doctor tiene specialty_id
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
        $user = Auth::user();
        $doctor = $user->doctor;

        if (!$doctor) {
            return redirect()->back()->with('error', 'No tienes un perfil médico asociado.');
        }

        // VALIDACIÓN DE BLOQUEO CORREGIDA (Comparando doctor_id)
        if (
            $order->doctor_id &&
            $order->doctor_id !== $doctor->id &&
            $order->claimed_at &&
            $order->claimed_at->gt(now()->subMinutes(20))
        ) {
            return redirect()->route('admin.doctor.panel')
                             ->with('error', 'Esta orden está siendo revisada por otro profesional.');
        }

        // Tomamos la orden: Actualizamos doctor_id con el ID del DOCTOR (no el del usuario)
        $order->update([
            'doctor_id' => $doctor->id,
            'claimed_at' => now()
        ]);

        Log::info("Médico ID: {$doctor->id} (User: {$user->id}) tomó la orden {$order->id}.");

        $order->load(['patient', 'doctor.user', 'examType', 'interactions']);
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

        $user = Auth::user();
        $doctor = $user->doctor;

        // Verificación de seguridad: Comparar contra doctor->id
        if ($order->doctor_id !== $doctor->id) {
            Log::error("Fallo de permiso en firma", ['order_doctor' => $order->doctor_id, 'my_doctor' => $doctor->id]);
            return redirect()->route('admin.doctor.panel')->with('error', 'No tienes permiso para firmar esta orden o el bloqueo expiró.');
        }

        try {
            DB::transaction(function () use ($order, $doctor, $request, $user) {
                $order->update([
                    'status'           => 'signed',
                    'signed_at'        => now(),
                    'clinical_context' => $request->clinical_context,
                    'claimed_at'       => null // Liberamos el bloqueo
                ]);

                // Vinculamos la transacción al médico (user_id) para pagos
                Transaction::where('reference_id', $order->id)
                    ->update(['receiver_id' => $user->id]);
            });

            return redirect()->route('admin.doctor.panel')->with('success', 'Orden firmada exitosamente.');

        } catch (\Exception $e) {
            Log::error("Error en processSignature: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al procesar la firma electrónica.');
        }
    }

    /**
     * Rechazar orden.
     */
    public function rejectOrder(Request $request, Order $order)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        // Si no es admin y no es el dueño del bloqueo, rechazar
        if (!$user->hasRole('admin') && (!$doctor || $order->doctor_id !== $doctor->id)) {
            Log::error("Fallo de permiso en rechazo", ['user' => $user->id, 'order_doc' => $order->doctor_id]);
            return redirect()->back()->with('error', 'No tienes permiso sobre esta orden.');
        }

        $request->validate(['rejection_reason' => 'required|string|max:500']);

        try {
            $order->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'claimed_at' => null,
            ]);

            Log::info("Orden {$order->id} rechazada por médico ID: " . ($doctor->id ?? 'Admin'));

            return redirect()->route('admin.doctor.panel')->with('warning', 'Orden rechazada correctamente.');
        } catch (\Exception $e) {
            Log::error("Error en rejectOrder: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al procesar el rechazo.');
        }
    }

    /**
     * Deriva la orden.
     */
    public function derivateOrder(Request $request, Order $order)
    {
        $request->validate([
            'specialty_id' => 'required|exists:specialties,id'
        ]);

        $doctor = auth()->user()->doctor;

        if ($doctor && $order->doctor_id === $doctor->id) {
            $order->update([
                'doctor_id' => null,
                'claimed_at' => null,
                // Aquí podrías actualizar una columna specialty_id en 'orders' si la tienes
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
        $doctor = $user->doctor;

        // Puede liberar el dueño del bloqueo o un admin
        if ($user->hasRole('admin') || ($doctor && $order->doctor_id === $doctor->id)) {
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
