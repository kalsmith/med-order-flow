<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Prescription;
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
        }, 'doctor.user', 'examType', 'prescriptions']);

        // 3. Lógica de filtrado para Doctores
        if ($user->hasRole('doctor')) {
            $doctor = $user->doctor;

            $query->where(function($q) use ($doctor) {
                // Órdenes ya tomadas por este doctor
                $q->where('doctor_id', $doctor->id)
                  ->whereIn('status', ['paid', 'pending', 'signed'])

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
     * Muestra el formulario de firma y bloquea la orden.
     */
    public function showSignForm(Order $order)
    {
        $user = Auth::user();
        $doctor = $user->doctor;

        if (!$doctor) {
            return redirect()->back()->with('error', 'No tienes un perfil médico asociado.');
        }

        // VALIDACIÓN DE BLOQUEO (Usando strval para evitar conflictos int/string)
        if (
            $order->doctor_id &&
            strval($order->doctor_id) !== strval($doctor->id) &&
            $order->claimed_at &&
            $order->claimed_at->gt(now()->subMinutes(20))
        ) {
            return redirect()->route('admin.doctor.panel')
                             ->with('error', 'Esta orden está siendo revisada por otro profesional.');
        }

        // Tomamos/Renovamos la orden para el médico actual
        $order->update([
            'doctor_id' => $doctor->id,
            'claimed_at' => now()
        ]);

        Log::info("Médico ID: {$doctor->id} tomó la orden {$order->id} para revisión.");

        $order->load(['patient', 'doctor.user', 'examType', 'prescriptions']);
        return view('admin.orders.sign', compact('order'));
    }

    /**
     * Procesa la firma digital actualizando la Prescripción y la Orden.
     */
    public function processSignature(Request $request, Order $order)
    {
        $request->validate([
            'clinical_context' => 'required|string|min:10'
        ]);

        $user = Auth::user();
        $doctor = $user->doctor;

        // Verificación de seguridad de identidad
        if (strval($order->doctor_id) !== strval($doctor->id)) {
            Log::error("Fallo de permiso en firma", ['order_doctor' => $order->doctor_id, 'my_doctor' => $doctor->id]);
            return redirect()->route('admin.doctor.panel')->with('error', 'No tienes permiso para firmar esta orden.');
        }

        try {
            DB::transaction(function () use ($order, $doctor, $request, $user) {

                // 1. Actualizar la Prescripción (El documento clínico real)
                $prescription = $order->prescriptions()->where('status', 'active')->first();

                if ($prescription) {
                    $prescription->update([
                        'status' => 'signed',
                        'signed_at' => now(),
                        'clinical_context' => $request->clinical_context,
                        'doctor_id' => $doctor->id
                    ]);
                } else {
                    // Fallback: Si no existe, se crea para no perder la información
                    $order->prescriptions()->create([
                        'doctor_id' => $doctor->id,
                        'exam_type_id' => $order->exam_type_id,
                        'status' => 'signed',
                        'signed_at' => now(),
                        'clinical_context' => $request->clinical_context,
                    ]);
                }

                // 2. Finalizar la Orden (Estado administrativo)
                $order->update([
                    'status'     => 'signed',
                    'claimed_at' => null // Liberamos bloqueo
                ]);

                // 3. Actualizar rotación del médico
                $doctor->update([
                    'last_assigned_at' => now()
                ]);

                // 4. Vincular transacción al médico para pagos
                Transaction::where('reference_id', $order->id)
                    ->update(['receiver_id' => $user->id]);
            });

            Log::info("Orden {$order->id} firmada exitosamente por Doctor {$doctor->id}");
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

        if (!$user->hasRole('admin') && (!$doctor || strval($order->doctor_id) !== strval($doctor->id))) {
            return redirect()->back()->with('error', 'No tienes permiso sobre esta orden.');
        }

        $request->validate(['rejection_reason' => 'required|string|max:500']);

        try {
            $order->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'claimed_at' => null,
            ]);

            Log::info("Orden {$order->id} rechazada.");
            return redirect()->route('admin.doctor.panel')->with('warning', 'Orden rechazada correctamente.');
        } catch (\Exception $e) {
            Log::error("Error en rejectOrder: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al procesar el rechazo.');
        }
    }

    /**
     * Libera manualmente una orden bloqueada.
     */
    public function releaseOrder(Request $request, Order $order)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        if ($user->hasRole('admin') || ($doctor && strval($order->doctor_id) === strval($doctor->id))) {
            $order->update([
                'doctor_id' => null,
                'claimed_at' => null
            ]);

            return redirect()->route('admin.doctor.panel')->with('success', 'Orden liberada correctamente.');
        }

        return redirect()->route('admin.doctor.panel')->with('error', 'No tienes permisos.');
    }
}
