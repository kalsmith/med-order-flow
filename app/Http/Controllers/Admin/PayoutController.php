<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PayoutController extends Controller
{
    /**
     * EL MÉDICO PIDE EL DINERO
     */
    public function requestStore(Request $request)
    {
        // En un caso real, aquí usas el doctor autenticado: $doctor = auth()->user()->doctor;
        // Por ahora simulamos que viene de un formulario
        $doctor = Doctor::findOrFail($request->doctor_id);

        $availableBalance = $doctor->balance; // Usamos el Accessor que creamos antes

        if ($availableBalance <= 0) {
            return back()->with('error', 'No tienes saldo disponible para retirar.');
        }

        // Creamos la solicitud de retiro por el total
        PayoutRequest::create([
            'doctor_id' => $doctor->id,
            'amount' => $availableBalance,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Solicitud de retiro enviada. Se procesará en breve.');
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
        $request->validate([
            'evidence' => 'required|image|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('evidence')) {
            // Guardamos el comprobante en storage/app/public/payouts
            $path = $request->file('evidence')->store('payouts', 'public');

            $payout->update([
                'status' => 'paid',
                'evidence_path' => $path,
                'paid_at' => now(),
                'admin_notes' => $request->admin_notes
            ]);
        }

        return back()->with('success', 'Pago marcado como completado y comprobante guardado.');
    }

    // En App\Http\Controllers\Admin\PayoutController.php

/**
 * VISTA PARA EL MÉDICO (Su Billetera)
 */
public function doctorWallet()
{
    // Obtenemos el médico asociado al usuario autenticado
    $doctor = auth()->user()->doctor;

    if (!$doctor) {
        return redirect()->route('admin.panel')->with('error', 'Perfil de médico no encontrado.');
    }

    return view('doctor.wallet', compact('doctor'));
}


}
