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



// App\Livewire\MedicalOrdersTable.php

public function render()
{
    $doctor = Auth::user()->doctor;
    $query = Order::with(['patient', 'examType', 'doctor.user', 'prescriptions']);

    if ($this->tab === 'available') {
        // En "Disponibles" vemos lo que nadie ha tomado + lo que YO tengo asignado pero no firmado
        $query->where(function($q) use ($doctor) {
            $q->availableForDoctor($doctor->id, $doctor->specialty_id)
              ->orWhere('doctor_id', $doctor->id);
        })
        // IMPORTANTE: Excluir explícitamente las ya firmadas de la pestaña "disponibles"
        ->whereDoesntHave('prescriptions', fn($p) => $p->where('status', 'signed'));

    } elseif ($this->tab === 'reentry') {
        // Solo MIS re-entradas
        $query->where('doctor_id', $doctor->id)->needsReentry();

    } else {
        // Pestaña Historial: Solo MIS órdenes finalizadas
        $query->where('doctor_id', $doctor->id)->inHistory();
    }

    return view('livewire.medical-orders-table', [
        'orders' => $query->latest('updated_at')->paginate(10)
    ]);
}


}
