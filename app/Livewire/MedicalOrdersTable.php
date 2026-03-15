<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class MedicalOrdersTable extends Component
{
    use WithPagination;

    public $tab = 'available'; // available, reentry, signed

    public function setTab($tab)
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $doctor = $user->doctor;
        $myDoctorId = $doctor->id ?? null;

        // 1. Garbage Collector (Se mantiene igual)
        Order::where('status', 'paid')
            ->whereNotNull('claimed_at')
            ->where('claimed_at', '<', now()->subMinutes(20))
            ->update(['doctor_id' => null, 'claimed_at' => null]);

        // 2. Query Base
        $query = Order::with([
            'patient' => fn($q) => $q->withTrashed(),
            'examType',
            'doctor.user',
            'activePrescription',
            'prescriptions'
        ])->withCount('interactions');

        // 3. Lógica de Pestañas
        if ($this->tab === 'available') {
            // SOLO órdenes nuevas (sin ninguna prescripción previa, ni firmadas)
            $query->where('status', 'paid')
                ->whereNull('signed_at')
                ->whereDoesntHave('prescriptions') // No tiene intentos previos
                ->where(function($q) use ($myDoctorId, $doctor) {
                    $q->where('doctor_id', $myDoctorId)
                      ->orWhere(function($sq) use ($doctor) {
                          $sq->whereNull('doctor_id')
                             ->whereHas('examType', fn($eq) => $eq->where('specialty_id', $doctor->specialty_id));
                      })
                      ->orWhere(function($sq) {
                          $sq->whereNull('doctor_id')->where('type', 'custom');
                      });
                });

        } elseif ($this->tab === 'reentry') {
            // SOLO órdenes para RE-FIRMA (tienen prescripciones anuladas y no están firmadas actualmente)
            $query->where('status', 'paid')
                ->whereNull('signed_at')
                ->whereHas('prescriptions', fn($q) => $q->where('status', 'voided'));

        } else {
            // HISTORIAL (Firmadas o Rechazadas)
            $query->where('doctor_id', $myDoctorId)
                  ->where(function($q) {
                      $q->whereNotNull('signed_at')
                        ->orWhereIn('status', ['rejected', 'refund_pending', 'refunded']);
                  });
        }

        return view('livewire.medical-orders-table', [
            'orders' => $query->latest('updated_at')->paginate(10)
        ]);
    }
}
