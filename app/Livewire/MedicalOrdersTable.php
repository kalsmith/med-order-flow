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
        $query = Order::with(['patient', 'examType', 'doctor.user', 'prescriptions']);

        if ($this->tab === 'available') {
            // DISPONIBLES: Lo que nadie ha tomado o lo que tengo asignado sin firmar
            $query->where(function($q) use ($doctor) {
                $q->availableForDoctor($doctor->id, $doctor->specialty_id)
                  ->orWhere('doctor_id', $doctor->id);
            })
            // Excluimos cualquier cosa que ya tenga una receta firmada
            ->whereDoesntHave('prescriptions', fn($p) => $p->where('status', 'signed'));

        } elseif ($this->tab === 'reentry') {
            // RE-ENTRY: Solo mis órdenes que fueron anuladas y esperan corrección
            $query->where('doctor_id', $doctor->id)->needsReentry();

        } elseif ($this->tab === 'standard') {
            // STANDARD: El flujo automático (Ejem: #1010 de César)
            // Filtramos por tu nuevo scope específico para este flujo
            $query->autoSignedStandard($doctor->id);

        } else {
            // FIRMADAS (Historial): Solo mis órdenes CUSTOM finalizadas
            // Así separamos la carga de trabajo manual de la automática
            $query->where('doctor_id', $doctor->id)
                  ->where('type', 'custom')
                  ->inHistory();
        }

        return view('livewire.medical-orders-table', [
            'orders' => $query->latest('updated_at')->paginate(10)
        ]);
    }
}
