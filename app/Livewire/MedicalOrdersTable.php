<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class MedicalOrdersTable extends Component
{
    use WithPagination;

    // Ahora manejamos 4 estados: disponibles, re-entrada, automáticas y firmadas (custom)
    public $tab = 'available'; // available, reentry, standard, signed

    public function setTab($tab)
    {
        $this->tab = $tab;
        $this->resetPage();
    }


    public function render()
{
    $doctor = Auth::user()->doctor;

    if (!$doctor) {
        return view('livewire.medical-orders-table', ['orders' => collect([])]);
    }

    $query = Order::query()->with(['patient', 'examType', 'doctor.user', 'prescriptions']);

    if ($this->tab === 'available') {
        $query->where(function($q) use ($doctor) {
            $q->availableForDoctor($doctor->id, $doctor->specialty_id)
              ->orWhere(function($sq) use ($doctor) {
                  $sq->where('doctor_id', $doctor->id)
                     ->where('status', 'paid')
                     ->whereDoesntHave('prescriptions', fn($p) => $p->where('status', 'signed'));
              });
        })->whereDoesntHave('prescriptions', fn($p) => $p->where('status', 'signed'));

    } elseif ($this->tab === 'reentry') {
        $query->where('doctor_id', $doctor->id)->needsReentry();

    } elseif ($this->tab === 'standard') {
        // Quitamos restricciones innecesarias para asegurar que la #1010 aparezca
        $query->where('doctor_id', $doctor->id)
              ->where('type', 'standard')
              ->where(function($q) {
                  $q->whereHas('prescriptions', fn($p) => $p->where('status', 'signed'))
                    ->orWhereNotNull('signed_at');
              });

    } else { // Historial (Signed)
        $query->where('doctor_id', $doctor->id)
              ->where('type', 'custom')
              ->inHistory();
    }

    return view('livewire.medical-orders-table', [
        'orders' => $query->latest('updated_at')->paginate(10)
    ]);
}



}
