<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\Doctor;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class PayoutController extends Controller
{
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
     * MOVIMIENTO 1: SOLICITUD (Status: pending)
     */
    public function requestStore(Request $request)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        if (!$doctor) return back()->with('error', 'Perfil de médico no encontrado.');

        $hasPending = PayoutRequest::where('doctor_id', $doctor->id)->where('status', 'pending')->exists();
        if ($hasPending) return back()->with('error', 'Ya tienes una solicitud de retiro en proceso.');

        $amount = (int) $doctor->balance;
        if ($amount <= 0) return back()->with('error', 'No tienes saldo disponible para retirar.');

        try {
            return DB::transaction(function () use ($doctor, $user, $amount) {
                $doctorRefresh = Doctor::where('id', $doctor->id)->lockForUpdate()->first();

                // 1. Restamos saldo para "congelar" el dinero
                $doctorRefresh->decrement('balance', $amount);

                // 2. Creamos la solicitud para el Admin
                $payout = PayoutRequest::create([
                    'doctor_id' => $doctor->id,
                    'amount' => $amount,
                    'status' => 'pending'
                ]);

                // 3. REGISTRO CONTABLE: Solicitud de Retiro (AMARILLO)
                Transaction::create([
                    'uuid' => (string) Str::uuid(),
                    'reference_code' => 'REQ-' . strtoupper(Str::random(8)),
                    'sender_id' => $user->id,
                    'receiver_id' => 1, // Hacia el sistema
                    'reference_id' => $payout->id,
                    'type' => 'payout',
                    'amount' => $amount,
                    'platform_fee' => 0,
                    'status' => 'pending',
                    'metadata' => json_encode([
                        'description' => 'Retiro de honorarios médicos solicitado',
                        'previous_balance' => $amount + $doctorRefresh->balance,
                        'new_balance' => $doctorRefresh->balance
                    ])
                ]);

                return back()->with('success', 'Solicitud enviada. Tu saldo se ha actualizado y el pago está en proceso de validación.');
            });
        } catch (Exception $e) {
            Log::error("Error en requestStore: " . $e->getMessage());
            return back()->with('error', 'Error al procesar la solicitud.');
        }
    }

    /**
     * MOVIMIENTO 2: PAGO EFECTIVO (Status: completed)
     */
    public function process(Request $request, PayoutRequest $payout)
    {
        $admin = auth()->user();
        $request->validate(['evidence' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048']);

        try {
            return DB::transaction(function () use ($request, $payout, $admin) {
                $path = $request->file('evidence')->store('payouts', 'public');

                // 1. Actualizamos la solicitud a pagada
                $payout->update([
                    'status' => 'paid',
                    'evidence_path' => $path,
                    'paid_at' => now(),
                    'admin_notes' => $request->admin_notes
                ]);

                // 2. IMPORTANTE: Creamos un NUEVO registro de transacción para el pago realizado
                // Esto es lo que aparecerá en VERDE como dinero efectivamente enviado.
                Transaction::create([
                    'uuid' => (string) Str::uuid(),
                    'reference_code' => 'PAY-' . strtoupper(Str::random(8)),
                    'sender_id' => 1, // Sale del sistema
                    'receiver_id' => $payout->doctor->user_id, // Llega al médico
                    'reference_id' => $payout->id,
                    'type' => 'payout',
                    'amount' => $payout->amount,
                    'platform_fee' => 0,
                    'status' => 'completed',
                    'metadata' => json_encode([
                        'description' => 'Transferencia de honorarios médicos realizada',
                        'admin_executor' => $admin->name
                    ])
                ]);

                return back()->with('success', 'Pago procesado exitosamente.');
            });
        } catch (Exception $e) {
            Log::error("Error en process: " . $e->getMessage());
            return back()->with('error', 'Error al procesar el registro del pago.');
        }
    }

    public function doctorWallet()
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        if (!$doctor) return redirect()->route('admin.panel')->with('error', 'Médico no encontrado.');

        // 1. Firmas (Ingresos)
        $signatures = $doctor->prescriptions()
            ->where('status', 'signed')
            ->with('order.patient.user')
            ->latest('signed_at')->take(20)->get()
            ->map(function($item) {
                $item->is_payment = false;
                $item->date_for_sort = $item->signed_at;
                $item->display_amount = in_array($item->type, ['custom', 'multiple']) ? 2800 : 1800;
                return $item;
            });

        // 2. Movimientos (Egresos y Pagos)
        // Buscamos donde el médico sea sender (solicitud) o receiver (pago recibido)
        $payments = Transaction::where(function($q) use ($user) {
                $q->where('receiver_id', $user->id)->orWhere('sender_id', $user->id);
            })
            ->where('type', 'payout')
            ->latest()->take(20)->get()
            ->map(function($item) {
                $item->is_payment = true;
                $item->date_for_sort = $item->created_at;
                $item->display_amount = $item->amount;
                return $item;
            });

        $combinedMovements = $signatures->concat($payments)
            ->sortByDesc('date_for_sort')->take(30);

        $payoutRequests = $doctor->payoutRequests()->latest()->take(10)->get();

        return view('doctor.wallet', compact('doctor', 'combinedMovements', 'payoutRequests'));
    }

    public function downloadEvidence(PayoutRequest $payout)
    {
        if (!$payout->evidence_path || !Storage::disk('public')->exists($payout->evidence_path)) abort(404);
        return Storage::disk('public')->response($payout->evidence_path);
    }
}
