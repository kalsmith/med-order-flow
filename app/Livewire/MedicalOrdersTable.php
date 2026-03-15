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
        $query->availableForDoctor($doctor->id, $doctor->specialty_id);
    } elseif ($this->tab === 'reentry') {
        $query->needsReentry();
    } else {
        $query->inHistory();
    }

    return view('livewire.medical-orders-table', [
        'orders' => $query->latest('updated_at')->paginate(10)
    ]);
}


}
