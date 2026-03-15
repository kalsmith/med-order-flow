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
        $doctor = Auth::user()->doctor;
        $query = Order::with(['patient', 'examType', 'doctor.user', 'prescriptions']);

        if ($this->tab === 'available') {
            // DISPONIBLES: Órdenes pagadas de su especialidad que NO tienen firmas ni anulaciones.
            $query->availableForDoctor($doctor->id, $doctor->specialty_id);

        } elseif ($this->tab === 'reentry') {
            // RE-ENTRY: Órdenes que tienen anulaciones pero no firmas.
            $query->needsReentry();

        } else {
            // HISTORIAL: Firmadas o rechazadas.
            $query->inHistory();
        }

        return view('livewire.medical-orders-table', [
            'orders' => $query->latest('updated_at')->paginate(10)
        ]);
    }

}
