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
        // 1. POR FIRMAR: Solo Custom que están pagadas y esperando
        $query->where('type', 'custom')
              ->where('status', 'paid');

    } elseif ($this->tab === 'auto_signed') {
        // 2. FIRMA AUTOMÁTICA: Son Standard, ya están firmadas y asignadas a él
        $query->where('type', 'standard')
              ->where('status', 'signed')
              ->where('doctor_id', $myDoctorId);

    } else {
        // 3. MIS FIRMADAS: Solo las Custom que él firmó manualmente
        $query->where('type', 'custom')
              ->where('status', 'signed')
              ->where('doctor_id', $myDoctorId);
    }

    return view('livewire.medical-orders-table', [
        'orders' => $query->latest()->paginate(10)
    ]);
}
}
