<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // Importante
use Exception;

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

    // 1. Verificación de existencia del perfil
    if (!$doctor) {
        Log::warning("Intento de retiro sin perfil médico. User ID: {$user->id}");
        return back()->with('error', 'No se encontró perfil médico vinculado.');
    }

    // 2. Verificación de solicitudes pendientes (Evita duplicados)
    $hasPending = PayoutRequest::where('doctor_id', $doctor->id)
        ->where('status', 'pending')
        ->exists();

    if ($hasPending) {
        return back()->with('error', 'Ya tienes una solicitud de pago en proceso. Espera a que sea procesada.');
    }

    // 3. Obtención del saldo (Usando el getBalanceAttribute que descuenta lo pendiente)
    $availableBalance = (int) $doctor->balance;

    if ($availableBalance <= 0) {
        Log::info("Médico intentó retirar sin saldo real. Doctor ID: {$doctor->id}");
        return back()->with('error', 'No tienes saldo disponible para retirar en este momento.');
    }

    // 4. (Opcional) Validación de monto mínimo para que no te pidan pagos de $1.800 cada hora
    if ($availableBalance < 5000) {
        return back()->with('info', 'El monto mínimo de retiro es de $5.000.');
    }

    try {
        // 5. Creación de la solicitud
        $payout = PayoutRequest::create([
            'doctor_id' => $doctor->id,
            'amount' => $availableBalance,
            'status' => 'pending'
        ]);

        Log::info("Solicitud de retiro creada con éxito.", [
            'doctor' => $user->name,
            'monto' => $availableBalance,
            'payout_id' => $payout->id
        ]);

        return back()->with('success', '¡Solicitud enviada! El monto ha sido bloqueado de tu saldo disponible y será procesado por administración.');

    } catch (Exception $e) {
        Log::error("Error crítico al crear solicitud de retiro: " . $e->getMessage(), [
            'doctor_id' => $doctor->id,
            'trace' => $e->getTraceAsString()
        ]);
        return back()->with('error', 'Hubo un error interno. Por favor, intenta más tarde.');
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
    public function process(Request $request, PayoutRequest $payout)
    {
        $admin = auth()->user();

        $request->validate([
            'evidence' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            if ($request->hasFile('evidence')) {
                // Guardamos el comprobante
                $path = $request->file('evidence')->store('payouts', 'public');

                $payout->update([
                    'status' => 'paid',
                    'evidence_path' => $path,
                    'paid_at' => now(),
                    'admin_notes' => $request->admin_notes
                ]);

                Log::info("Pago procesado exitosamente. Admin: {$admin->name}, Doctor: {$payout->doctor->user->name}, Monto: {$payout->amount}, File: {$path}");
            }

            return back()->with('success', 'Pago marcado como completado y comprobante guardado.');

        } catch (Exception $e) {
            Log::error("Error al procesar el pago del médico. Request ID: {$payout->id}, Error: " . $e->getMessage());
            return back()->with('error', 'Error al procesar el registro del pago.');
        }
    }

    /**
     * VISTA PARA EL MÉDICO (Su Billetera)
     */
    public function doctorWallet()
    {
        $doctor = auth()->user()->doctor;

        if (!$doctor) {
            return redirect()->route('admin.panel')->with('error', 'Perfil de médico no encontrado.');
        }

        // Traemos las últimas 15 firmas con su relación de orden
        $recentSignatures = $doctor->prescriptions()
            ->where('status', 'signed')
            ->with('order')
            ->latest('signed_at')
            ->take(15)
            ->get();

        return view('doctor.wallet', compact('doctor', 'recentSignatures'));
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
