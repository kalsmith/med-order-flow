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

    if (!$doctor) return back()->with('error', 'Perfil no encontrado.');

    // Evitar duplicados
    if (PayoutRequest::where('doctor_id', $doctor->id)->where('status', 'pending')->exists()) {
        return back()->with('error', 'Ya tienes una solicitud pendiente.');
    }

    $amount = (int) $doctor->balance;

    if ($amount <= 0) return back()->with('error', 'No hay saldo.');

    try {
        return \DB::transaction(function () use ($doctor, $user, $amount) {
            // 1. Crear solicitud administrativa
            $payout = PayoutRequest::create([
                'doctor_id' => $doctor->id,
                'amount' => $amount,
                'status' => 'pending'
            ]);

            // 2. Crear Transacción contable de salida
            Transaction::create([
                'sender_id' => null, // Sale de la plataforma
                'receiver_id' => $user->id,
                'reference_id' => $payout->id, // ID numérico del payout
                'type' => 'payout', // Debes asegurarte que el ENUM de la DB lo soporte o sea string
                'amount' => $amount,
                'platform_fee' => 0,
                'status' => 'pending',
                'metadata' => [
                    'description' => 'Retiro de honorarios médicos',
                    'method' => 'Transferencia Bancaria'
                ]
            ]);

            return back()->with('success', 'Solicitud creada. El movimiento aparece ahora en tu historial.');
        });
    } catch (\Exception $e) {
        Log::error("Error en Payout: " . $e->getMessage());
        return back()->with('error', 'Error al procesar la solicitud.');
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
        return \DB::transaction(function () use ($request, $payout, $admin) {

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
            \App\Models\Transaction::where('reference_id', $payout->id)
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

    // 1. Traemos las últimas 15 firmas (INGRESOS)
    // Usamos with('order') para saber si es Custom o Standard y calcular el precio en la vista
    $recentSignatures = $doctor->prescriptions()
        ->where('status', 'signed')
        ->with('order.patient.user')
        ->latest('signed_at')
        ->take(15)
        ->get();

    // 2. Traemos las últimas solicitudes de pago (EGRESOS / RETIROS)
    // Esto es lo que faltaba para que la tabla de "Estado de solicitudes" se llene
    $payoutRequests = $doctor->payoutRequests()
        ->latest()
        ->take(10)
        ->get();

    return view('doctor.wallet', compact('doctor', 'recentSignatures', 'payoutRequests'));
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
