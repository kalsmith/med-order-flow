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
        // NUEVAS: Pagadas, sin dueño (o mías) y NUNCA han tenido prescripciones
        $query->where('status', 'paid')
            ->whereDoesntHave('prescriptions')
            ->where(function($q) use ($myDoctorId) {
                $q->where('doctor_id', $myDoctorId)->orWhereNull('doctor_id');
            });

    } elseif ($this->tab === 'reentry') {
        // POR RE-FIRMAR: Tienen algo anulado Y la prescripción actual NO está firmada
        // Ignoramos el signed_at de la orden porque puede ser "basura" de la versión anterior
        $query->where('status', 'paid')
            ->whereHas('prescriptions', fn($q) => $q->where('status', 'voided'))
            ->whereHas('prescriptions', fn($q) => $q->where('status', '!=', 'signed'));

    } else {
        // HISTORIAL: La prescripción más reciente SÍ está firmada (o fue rechazada/reembolsada)
        $query->where(function($q) use ($myDoctorId) {
            $q->where('doctor_id', $myDoctorId)
              ->whereHas('prescriptions', fn($sq) => $sq->where('status', 'signed'))
              ->orWhereIn('status', ['rejected', 'refund_pending', 'refunded']);
        });
    }

    return view('livewire.medical-orders-table', [
        'orders' => $query->latest('updated_at')->paginate(10)
    ]);
}



}
