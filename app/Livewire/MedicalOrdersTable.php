<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order; // <--- Cambiado de MedicalOrder a Order
use Illuminate\Support\Facades\Auth;

class MedicalOrdersTable extends Component
{
    use WithPagination;

    public $tab = 'available';

    protected $paginationTheme = 'bootstrap';

    public function setTab($tab)
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function render()
{
    $user = Auth::user();
    $myDoctorId = $user->doctor->id ?? null;

    // 1. Garbage Collector (Se mantiene igual, está perfecto)
    Order::whereIn('status', ['pending', 'paid'])
        ->whereNotNull('claimed_at')
        ->where('claimed_at', '<', now()->subMinutes(20))
        ->update([
            'doctor_id' => null,
            'claimed_at' => null
        ]);

    $query = Order::with(['patient', 'examType', 'doctor.user']);

    if ($this->tab === 'available') {
        $query->where('status', 'paid') // Solo lo pagado
              ->where(function($q) use ($myDoctorId) {
                  $q->where('doctor_id', $myDoctorId) // Lo que ya tomé
                    ->orWhere(function($sq) {
                        $sq->whereNull('doctor_id') // O lo que nadie ha tomado
                          ->where('type', 'custom');
                    })
                    // Agregamos seguridad extra: si está tomado por otro hace menos de 20 min, NO mostrar
                    ->where(function($sq) use ($myDoctorId) {
                        $sq->where('doctor_id', $myDoctorId)
                          ->orWhereNull('doctor_id')
                          ->orWhere('claimed_at', '<', now()->subMinutes(20));
                    });
              });
    } else {
        $query->where('status', 'signed')
              ->where('doctor_id', $myDoctorId);
    }

    return view('livewire.medical-orders-table', [
        'orders' => $query->latest()->paginate(10)
    ]);
}


}
