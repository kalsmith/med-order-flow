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
    public function requestStore(Request $request)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        if (!$doctor) {
            Log::warning("Intento de solicitud de retiro sin perfil de médico. User ID: {$user->id}");
            return back()->with('error', 'No se encontró perfil médico.');
        }

        $availableBalance = $doctor->balance;

        if ($availableBalance <= 0) {
            Log::info("Médico intentó retirar sin saldo. Doctor ID: {$doctor->id}, Nombre: {$user->name}");
            return back()->with('error', 'No tienes saldo disponible.');
        }

        try {
            $payout = PayoutRequest::create([
                'doctor_id' => $doctor->id,
                'amount' => $availableBalance,
                'status' => 'pending'
            ]);

            Log::info("Solicitud de retiro creada. Doctor: {$user->name}, Monto: {$availableBalance}, Request ID: {$payout->id}");

            return back()->with('success', 'Solicitud enviada correctamente.');

        } catch (Exception $e) {
            Log::error("Error al crear solicitud de retiro: " . $e->getMessage(), [
                'doctor_id' => $doctor->id,
                'amount' => $availableBalance
            ]);
            return back()->with('error', 'Hubo un problema al procesar tu solicitud.');
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

        return view('doctor.wallet', compact('doctor'));
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
