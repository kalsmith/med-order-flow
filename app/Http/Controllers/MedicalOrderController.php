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

    // 2. Query Base: Cargamos interacciones y la receta activa específicamente
    $query = Order::with([
        'patient' => fn($q) => $q->withTrashed(),
        'doctor.user',
        'examType',
        'activePrescription', // Usamos tu relación definida con latestOfMany
    ])->withCount('interactions'); // Agregamos conteo de mensajes

    // 3. Lógica de filtrado para Doctores
    if ($user->hasRole('doctor')) {
        $doctor = $user->doctor;

        $query->where(function($q) use ($doctor) {
            // A. Órdenes ya tomadas por este doctor
            $q->where('doctor_id', $doctor->id)
              ->whereIn('status', ['paid', 'pending', 'signed'])

            // B. O órdenes pagadas de su especialidad disponibles
            ->orWhere(function($sq) use ($doctor) {
                $sq->whereNull('doctor_id')
                   ->where('status', 'paid')
                   ->whereHas('examType', function($eq) use ($doctor) {
                       $eq->where('specialty_id', $doctor->specialty_id);
                   });
            })

            // C. O solicitudes especiales (custom) pagadas disponibles
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

        // VALIDACIÓN DE BLOQUEO SEGURA
        $currentDoctorId = $order->doctor_id ? strval($order->doctor_id) : null;
        $myId = strval($doctor->id);

        if (
            $currentDoctorId &&
            $currentDoctorId !== $myId &&
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

        Log::info("Médico ID: {$doctor->id} inició revisión de Orden {$order->id}");

        // Cargamos la relación específica 'prescription' (el HasOne que añadimos antes)
       // $order->load(['patient', 'doctor.user', 'examType', 'prescription']);
        $order->load(['patient', 'activePrescription', 'examType', 'interactions']);

        return view('admin.orders.sign', compact('order'));
    }

    /**
     * Procesa la firma digital actualizando la Prescripción y la Orden.
     */


public function processSignature(Request $request, Order $order)
{
    Log::info("--- INICIO PROCESO FIRMA ---", ['order_id' => $order->id]);

    $request->validate([
        'clinical_context' => 'required|string|min:10'
    ]);

    $user = Auth::user();
    $doctor = $user->doctor;

    try {
        DB::transaction(function () use ($order, $doctor, $request, $user) {

            // 1. Buscar prescripción activa
            $prescription = $order->prescriptions()
                ->where('status', 'active')
                ->first();

            if ($prescription) {
                Log::info("Prescripción encontrada", ['id' => $prescription->id, 'correlativo' => $prescription->correlative_number]);

                $updated = $prescription->update([
                    'doctor_id' => $doctor->id,
                    'status' => 'signed',
                    'signed_at' => now(),
                    'clinical_context' => $request->clinical_context,
                    'type' => $order->type === 'custom' ? 'custom' : 'standard',
                ]);

                Log::info("Resultado update prescripción", ['success' => $updated]);
            } else {
                Log::warning("No se encontró prescripción activa, creando una nueva.");
                $newPrescription = $order->prescriptions()->create([
                    'doctor_id' => $doctor->id,
                    'exam_type_id' => $order->exam_type_id,
                    'status' => 'signed',
                    'signed_at' => now(),
                    'clinical_context' => $request->clinical_context,
                    'type' => $order->type === 'custom' ? 'custom' : 'standard',
                ]);
                Log::info("Nueva prescripción creada", ['id' => $newPrescription->id]);
            }

            // 2. Actualizar Orden
            // Forzamos el guardado con save() por si el update() ignora los cambios
            $order->signed_at = now();
            $order->claimed_at = null;
            $order_saved = $order->save();

            Log::info("Orden actualizada", [
                'id' => $order->id,
                'signed_at' => $order->signed_at,
                'success' => $order_saved
            ]);

            // 3. Actividad Médico
            $doctor->update(['last_assigned_at' => now()]);
            Log::info("Actividad médico actualizada");

        });

        Log::info("--- TRANSACCIÓN COMPLETADA ---");
        return redirect()->route('admin.doctor.panel')->with('success', 'Documento firmado exitosamente.');

    } catch (\Exception $e) {
        Log::error("ERROR CRÍTICO EN FIRMA", [
            'mensaje' => $e->getMessage(),
            'linea' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->back()->with('error', 'Error interno: ' . $e->getMessage());
    }
}


    /**
     * Rechazar orden por motivos clínicos o técnicos.
     */
    public function rejectOrder(Request $request, Order $order)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        if (!$user->hasRole('admin') && (!$doctor || strval($order->doctor_id) !== strval($doctor->id))) {
            return redirect()->back()->with('error', 'No tiene permisos para rechazar esta orden.');
        }

        $request->validate(['rejection_reason' => 'required|string|max:500']);

        try {
            $order->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'claimed_at' => null,
            ]);

            Log::warning("Orden {$order->id} rechazada por médico {$user->id}. Motivo: {$request->rejection_reason}");
            return redirect()->route('admin.doctor.panel')->with('warning', 'La orden ha sido rechazada.');
        } catch (\Exception $e) {
            Log::error("Error en rejectOrder: " . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo procesar el rechazo.');
        }
    }

    /**
     * Libera manualmente el bloqueo de una orden (Admin o el propio Médico).
     */
    public function releaseOrder(Order $order)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        $isOwner = $doctor && strval($order->doctor_id) === strval($doctor->id);

        if ($user->hasRole('admin') || $isOwner) {
            $order->update([
                'doctor_id' => null,
                'claimed_at' => null
            ]);

            return redirect()->route('admin.doctor.panel')->with('success', 'La orden está disponible nuevamente.');
        }

        return redirect()->route('admin.doctor.panel')->with('error', 'Acción no autorizada.');
    }
}
