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

    $query = Order::with(['patient' => fn($q) => $q->withTrashed(), 'examType', 'doctor.user', 'activePrescription', 'prescriptions'])
        ->withCount('interactions');

    if ($this->tab === 'available') {
        // DISPONIBLES: Órdenes pagadas que NO tienen ninguna prescripción FIRMADA ni ANULADA.
        // Esto incluye órdenes sin prescripciones y órdenes con prescripciones en estado 'active'.
        $query->where('status', 'paid')
            ->whereDoesntHave('prescriptions', function($q) {
                $q->whereIn('status', ['signed', 'voided']);
            })
            ->where(function($q) use ($myDoctorId) {
                $q->where('doctor_id', $myDoctorId)->orWhereNull('doctor_id');
            });

    } elseif ($this->tab === 'reentry') {
        // POR RE-FIRMAR: Tiene al menos una anulada (voided) PERO aún no tiene ninguna firmada.
        $query->where('status', 'paid')
            ->whereHas('prescriptions', fn($q) => $q->where('status', 'voided'))
            ->whereDoesntHave('prescriptions', fn($q) => $q->where('status', 'signed'));

    } else {
        // HISTORIAL: Ya tiene una firmada O la orden fue rechazada/reembolsada globalmente.
        $query->where(function($q) use ($myDoctorId) {
            $q->whereHas('prescriptions', fn($sq) => $sq->where('status', 'signed'))
              ->orWhereIn('status', ['rejected', 'refund_pending', 'refunded']);
        });
    }

    return view('livewire.medical-orders-table', [
        'orders' => $query->latest('updated_at')->paginate(10)
    ]);
}


}
