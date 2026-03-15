<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Prescription;
use App\Models\Transaction;
use App\Services\RefundService;
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

    // 1. Garbage Collector: Liberamos bloqueos expirados
    // Solo liberamos órdenes que estén en proceso (pending/paid) y NO firmadas ni rechazadas.
    Order::whereIn('status', ['pending', 'paid'])
        ->whereNotNull('claimed_at')
        ->where('claimed_at', '<', now()->subMinutes(20))
        ->update([
            'doctor_id' => null,
            'claimed_at' => null
        ]);

    // 2. Query Base
    $query = Order::with([
        'patient' => fn($q) => $q->withTrashed(),
        'doctor.user',
        'examType',
        'activePrescription',
    ])->withCount('interactions');

    // 3. Lógica de filtrado para Doctores
    if ($user->hasRole('doctor')) {
        $doctor = $user->doctor;

        $query->where(function($q) use ($doctor) {
            // A. Órdenes del doctor: Incluimos 'rejected' y 'signed' para que el historial sea visible
            $q->where('doctor_id', $doctor->id)
              ->whereIn('status', ['paid', 'pending', 'signed', 'rejected'])

            // B. Órdenes de su especialidad disponibles para tomar (sin doctor asignado)
            ->orWhere(function($sq) use ($doctor) {
                $sq->whereNull('doctor_id')
                   ->where('status', 'paid')
                   ->whereHas('examType', function($eq) use ($doctor) {
                       $eq->where('specialty_id', $doctor->specialty_id);
                   });
            })

            // C. Solicitudes especiales (custom) disponibles para tomar (sin doctor asignado)
            ->orWhere(function($sq) {
                $sq->whereNull('doctor_id')
                   ->where('type', 'custom')
                   ->where('status', 'paid');
            });
        });
    }

    // Ordenamos por fecha de actualización para que lo último trabajado suba,
    // pero puedes mantener latest() si prefieres orden por creación.
    $orders = $query->latest('updated_at')->paginate(10);

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


use App\Services\RefundService; // No olvides importar el servicio

/**
 * Inyectamos el RefundService en el método o en el constructor
 */
public function rejectOrder(Request $request, Order $order, RefundService $refundService)
{
    $user = auth()->user();
    $doctor = $user->doctor;

    Log::info("=== INICIO PROCESO RECHAZO CON REEMBOLSO ===", [
        'order_id' => $order->id,
        'flow_order_id' => $order->flow_order_id
    ]);

    // 1. Validación de permisos
    if (!$user->hasRole('admin') && (!$doctor || strval($order->doctor_id) !== strval($doctor->id))) {
        return redirect()->back()->with('error', 'No tiene permisos para rechazar esta orden.');
    }

    $request->validate(['rejection_reason' => 'required|string|max:500']);

    try {
        DB::beginTransaction();

        // 2. Actualizar la Orden localmente a 'rejected'
        $order->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'claimed_at' => null,
        ]);

        // 3. Actualizar la Prescripción asociada
        $prescription = Prescription::where('order_id', $order->id)->first();
        if ($prescription) {
            $prescription->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'doctor_id' => $doctor->id ?? $prescription->doctor_id
            ]);
        }

        DB::commit();

        // --- PROCESO DE REEMBOLSO ---
        // Verificamos si tenemos el ID de transacción de Flow
        $flowTrxId = $order->flow_order_id;

        if ($flowTrxId) {
            Log::info("Disparando solicitud de reembolso...", ['flowTrxId' => $flowTrxId]);

            $refundResponse = $refundService->createRefund($order, $flowTrxId);

            if ($refundResponse) {
                Log::info("Reembolso procesado correctamente en Flow", ['token' => $refundResponse->token]);
                $message = 'La orden ha sido rechazada y el reembolso ha sido solicitado con éxito.';
            } else {
                Log::error("El rechazo se hizo pero el reembolso en Flow falló.");
                $message = 'La orden se rechazó, pero hubo un problema con el reembolso automático. Por favor, revisar en panel de Flow.';
            }
        } else {
            Log::warning("No se encontró flow_order_id para esta orden. No se puede procesar reembolso automático.");
            $message = 'La orden se rechazó, pero no se encontró registro de pago para reembolso automático.';
        }

        return redirect()->route('admin.doctor.panel')->with('warning', $message);

    } catch (\Exception $e) {
        if (DB::transactionLevel() > 0) DB::rollBack();

        Log::error("ERROR CRÍTICO EN RECHAZO", ['error' => $e->getMessage()]);
        return redirect()->back()->with('error', 'Error al procesar el rechazo: ' . $e->getMessage());
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

    /**
 * Deriva la orden a una especialidad específica y la libera del médico actual.
 */
public function derivateOrder(Request $request, Order $order)
{
    $request->validate([
        'specialty_id' => 'required|exists:specialties,id'
    ]);

    try {
        // Asumiendo que las órdenes 'custom' necesitan un exam_type
        // o que manejas la especialidad directamente en la orden.
        // Aquí liberamos al doctor para que aparezca en la lista global de esa especialidad.
        $order->update([
            'doctor_id' => null,
            'claimed_at' => null,
            // Si tu tabla orders tiene specialty_id, úsalo aquí.
            // Si no, podrías necesitar asociar un exam_type genérico de esa especialidad.
            // 'specialty_id' => $request->specialty_id,
        ]);

        Log::info("Orden {$order->id} derivada a especialidad {$request->specialty_id}");

        return redirect()->route('admin.doctor.panel')->with('success', 'La orden ha sido derivada correctamente.');
    } catch (\Exception $e) {
        Log::error("Error en derivateOrder: " . $e->getMessage());
        return redirect()->back()->with('error', 'No se pudo procesar la derivación.');
    }
}


}
