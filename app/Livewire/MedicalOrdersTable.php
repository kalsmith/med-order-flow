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
    // Iniciamos el query limpio
    $query = Order::query()->with(['patient', 'examType', 'doctor.user', 'prescriptions']);

    if ($this->tab === 'available') {
        // DISPONIBLES: Nadie las ha tomado O las tengo yo pero no he firmado
        $query->where(function($q) use ($doctor) {
            $q->availableForDoctor($doctor->id, $doctor->specialty_id)
              ->orWhere(function($sq) use ($doctor) {
                  $sq->where('doctor_id', $doctor->id)
                     ->whereDoesntHave('prescriptions', fn($p) => $p->where('status', 'signed'));
              });
        })
        // Filtro de seguridad: En disponibles NUNCA debe haber algo con receta firmada
        ->whereDoesntHave('prescriptions', fn($p) => $p->where('status', 'signed'));

    } elseif ($this->tab === 'reentry') {
        $query->where('doctor_id', $doctor->id)->needsReentry();

    } elseif ($this->tab === 'standard') {
        // PESTAÑA CLAVE: Aquí es donde César verá la #1010
        $query->where('doctor_id', $doctor->id)
              ->where('type', 'standard')
              ->whereHas('prescriptions', fn($p) => $p->where('status', 'signed'));

    } else {
        // HISTORIAL: Solo mis órdenes CUSTOM (las que yo hice a mano)
        $query->where('doctor_id', $doctor->id)
              ->where('type', 'custom')
              ->inHistory();
    }

    return view('livewire.medical-orders-table', [
        'orders' => $query->latest('updated_at')->paginate(10)
    ]);
}




}
