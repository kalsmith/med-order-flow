<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MedicalOrder;
use Illuminate\Support\Facades\Auth;

class MedicalOrdersTable extends Component
{
    use WithPagination;

    public $tab = 'available'; // Tab por defecto

    protected $paginationTheme = 'bootstrap';

    // Resetear página al cambiar de pestaña
    public function setTab($tab)
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $myDoctorId = $user->doctor->id ?? null;

        $query = MedicalOrder::with(['patient', 'examType', 'doctor.user']);

        if ($this->tab === 'available') {
            // Pestaña 1: Todo lo que el doctor puede firmar (pagado y tipo custom)
            $query->where('type', 'custom')
                ->where('status', 'paid');
        } else {
            // Pestaña 2: Todo lo que ya firmó (independiente de si fue manual o automático)
            $query->where('status', 'signed')
                ->where('doctor_id', $myDoctorId);
        }

        return view('livewire.medical-orders-table', [
            'orders' => $query->latest()->paginate(10)
        ]);
    }
}
