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
        // DISPONIBLES O TOMADAS PERO NO FIRMADAS
        $query->where(function($q) use ($doctor) {
            $q->availableForDoctor($doctor->id, $doctor->specialty_id)
              ->orWhere(function($sq) use ($doctor) {
                  $sq->where('doctor_id', $doctor->id)
                     ->where('status', 'paid')
                     ->whereDoesntHave('prescriptions', fn($p) => $p->where('status', 'signed'));
              });
        })->whereDoesntHave('prescriptions', fn($p) => $p->where('status', 'signed'));

    } elseif ($this->tab === 'reentry') {
        // Órdenes que requieren corrección
        $query->where('doctor_id', $doctor->id)->needsReentry();

    } elseif ($this->tab === 'standard') {
        // Pestaña donde vive la #1010: Filtrado por doctor_id en la receta vía Scope
        $query->autoSignedStandard($doctor->id);

    } else {
        // Historial Personal: Solo mis órdenes CUSTOM (manuales) firmadas o finalizadas
        $query->where('doctor_id', $doctor->id)
              ->where('type', 'custom')
              ->inHistory();
    }

    return view('livewire.medical-orders-table', [
        'orders' => $query->latest('updated_at')->paginate(10)
    ]);
}



}
