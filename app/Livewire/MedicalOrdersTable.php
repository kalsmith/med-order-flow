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
            $query->where('status', 'paid')
                ->where(function($q) use ($myDoctorId) {
                    $q->whereNull('doctor_id')
                        ->orWhere('doctor_id', $myDoctorId)
                        ->orWhere('claimed_at', '<', now()->subMinutes(20));
                });
        } elseif ($this->tab === 'auto_signed') {
            // NUEVA PESTAÑA: Firmadas por el sistema (sin intervención de doctor)
            $query->where('status', 'signed')
                ->whereNull('doctor_id');
        } else {
            // Mis Firmadas: Solo las que YO firmé
            $query->where('status', 'signed')
                ->where('doctor_id', $myDoctorId);
        }

        return view('livewire.medical-orders-table', [
            'orders' => $query->latest()->paginate(10)
        ]);
    }
}
