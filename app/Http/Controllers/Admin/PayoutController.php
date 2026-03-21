<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\Doctor;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // Importante
use Exception;
use Illuminate\Support\Facades\DB;

class PayoutController extends Controller
{
    /**
     * EL MÉDICO PIDE EL DINERO
     */
/**
 * EL MÉDICO PIDE EL DINERO
 */

public function requestStore(Request $request)
{
    $user = auth()->user();
    $doctor = $user->doctor;

    if (!$doctor) {
        return back()->with('error', 'Perfil de médico no encontrado.');
    }

    // 1. Evitar duplicados (Control de concurrencia)
    $hasPending = PayoutRequest::where('doctor_id', $doctor->id)
        ->where('status', 'pending')
        ->exists();

    if ($hasPending) {
        return back()->with('error', 'Ya tienes una solicitud de retiro en proceso.');
    }

    $amount = (int) $doctor->balance;

    if ($amount <= 0) {
        return back()->with('error', 'No tienes saldo disponible para retirar.');
    }

    try {
        return DB::transaction(function () use ($doctor, $user, $amount) {

            // --- BLOQUEO PARA EVITAR RACE CONDITIONS ---
            // Re-obtenemos el doctor con lock para asegurar que el saldo no cambió en milisegundos
            $doctorRefresh = Doctor::where('id', $doctor->id)->lockForUpdate()->first();

            // 2. RESTAR EL SALDO INMEDIATAMENTE
            // El dinero sale del "Disponible" y queda en manos del "Admin" (virtualmente)
            $doctorRefresh->decrement('balance', $amount);

            // 3. Crear la solicitud administrativa
            $payout = PayoutRequest::create([
                'doctor_id' => $doctor->id,
                'amount' => $amount,
                'status' => 'pending'
            ]);

            // 4. Registrar la transacción (Aparecerá en AMARILLO en la cartola)
            Transaction::create([
                'sender_id' => 1, // Sistema/Admin
                'receiver_id' => $user->id,
                'reference_id' => $payout->id,
                'type' => 'payout',
                'amount' => $amount,
                'platform_fee' => 0,
                'status' => 'pending', // <--- Importante: nace como pending
                'metadata' => [
                    'description' => 'Retiro de honorarios médicos solicitado',
                    'previous_balance' => $amount + $doctorRefresh->balance,
                    'new_balance' => $doctorRefresh->balance
                ]
            ]);

            Log::info("Solicitud de retiro y descuento de saldo: Doctor ID {$doctor->id}, Monto {$amount}");

            return back()->with('success', 'Solicitud enviada. Tu saldo se ha actualizado y el pago está en proceso de validación.');
        });

    } catch (\Exception $e) {
        Log::error("Error al procesar solicitud de retiro: " . $e->getMessage());
        return back()->with('error', 'Ocurrió un error al procesar tu solicitud.');
    }
}


    /**
     * TÚ (ADMIN) VES TODAS LAS SOLICITUDES
     */
    public function index()
    {
        $pendingPayouts = PayoutRequest::with('doctor.user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $historyPayouts = PayoutRequest::with('doctor.user')
            ->where('status', '!=', 'pending')
            ->latest()
            ->paginate(10);

        return view('admin.payouts.index', compact('pendingPayouts', 'historyPayouts'));
    }

    /**
     * TÚ (ADMIN) MARCAS COMO PAGADO Y SUBES COMPROBANTE
     */
/**
 * TÚ (ADMIN) MARCAS COMO PAGADO Y SUBES COMPROBANTE
 */
public function process(Request $request, PayoutRequest $payout)
{
    $admin = auth()->user();

    $request->validate([
        'evidence' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        'admin_notes' => 'nullable|string|max:500'
    ]);

    try {
        return DB::transaction(function () use ($request, $payout, $admin) {

            if (!$request->hasFile('evidence')) {
                throw new \Exception("El comprobante de pago es obligatorio.");
            }

            // 1. Guardar el archivo físicamente
            $path = $request->file('evidence')->store('payouts', 'public');

            // 2. Actualizar la solicitud administrativa (PayoutRequest)
            $payout->update([
                'status' => 'paid',
                'evidence_path' => $path,
                'paid_at' => now(),
                'admin_notes' => $request->admin_notes
            ]);

            // 3. Actualizar la transacción contable vinculada
            // Buscamos la transacción que tenga el ID de este payout como referencia
            Transaction::where('reference_id', $payout->id)
                ->where('receiver_id', $payout->doctor->user_id)
                ->where('type', 'payout')
                ->update([
                    'status' => 'completed',
                    'updated_at' => now()
                ]);

            Log::info("Pago y Transacción completados exitosamente.", [
                'admin' => $admin->name,
                'doctor' => $payout->doctor->user->name,
                'monto' => $payout->amount,
                'payout_id' => $payout->id
            ]);

            return back()->with('success', 'Pago procesado: El saldo ahora figura como pagado en el historial del médico.');
        });

    } catch (\Exception $e) {
        Log::error("Error al procesar el pago del médico. ID: {$payout->id}, Error: " . $e->getMessage());
        return back()->with('error', 'Error al procesar el registro del pago: ' . $e->getMessage());
    }
}

    /**
     * VISTA PARA EL MÉDICO (Su Billetera)
     */
public function doctorWallet()
{
    $user = auth()->user();
    $doctor = $user->doctor;

    if (!$doctor) {
        return redirect()->route('admin.panel')->with('error', 'Perfil de médico no encontrado.');
    }

    // 1. Obtener las firmas (Igual que antes)
    $signatures = $doctor->prescriptions()
        ->where('status', 'signed')
        ->with('order.patient.user')
        ->latest('signed_at')
        ->take(20)
        ->get()
        ->map(function($item) {
            $item->is_payment = false;
            $item->date_for_sort = $item->signed_at;
            $item->display_amount = in_array($item->order->type, ['custom', 'multiple']) ? 2800 : 1800;
            return $item;
        });

    // 2. Obtener los movimientos de retiro (Pendientes y Completados)
    $payments = Transaction::where('receiver_id', $user->id)
        ->where('type', 'payout')
        ->whereIn('status', ['pending', 'completed']) // <-- Traemos ambos
        ->latest()
        ->take(10)
        ->get()
        ->map(function($item) {
            $item->is_payment = true;
            $item->date_for_sort = $item->created_at;
            $item->display_amount = $item->amount;
            return $item;
        });

    // 3. Unificar y ordenar
    $combinedMovements = $signatures->concat($payments)
        ->sortByDesc('date_for_sort')
        ->take(25);

    $payoutRequests = $doctor->payoutRequests()->latest()->take(10)->get();

    return view('doctor.wallet', compact('doctor', 'combinedMovements', 'payoutRequests'));
}

public function downloadEvidence(PayoutRequest $payout)
{
    // Verificamos si el archivo existe físicamente
    if (!$payout->evidence_path || !Storage::disk('public')->exists($payout->evidence_path)) {
        Log::error("Archivo no encontrado: " . $payout->evidence_path);
        abort(404, 'El archivo no existe en el servidor.');
    }

    // Retornamos el archivo directamente desde el storage interno
    return Storage::disk('public')->response($payout->evidence_path);
}


}
