<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
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
    $doctor = $user->doctor;
    $myDoctorId = $doctor->id ?? null;

    // 1. Garbage Collector (Mantenemos tu lógica, pero aseguramos no liberar lo rechazado)
    Order::whereIn('status', ['pending', 'paid'])
        ->whereNotNull('claimed_at')
        ->where('claimed_at', '<', now()->subMinutes(20))
        ->update([
            'doctor_id' => null,
            'claimed_at' => null
        ]);

    // 2. Query Base
    $query = Order::with([
        'patient' => fn($q) => $q->withTrashed(),
        'examType',
        'doctor.user',
        'activePrescription'
    ])->withCount('interactions');

    if ($this->tab === 'available') {
        // Pestaña Pendientes: Lo que está pagado y NO firmado/rechazado
        $query->where('status', 'paid')
            ->whereNull('signed_at')
            ->where(function($q) use ($myDoctorId, $doctor) {
                $q->where('doctor_id', $myDoctorId)
                  ->orWhere(function($sq) use ($doctor) {
                      $sq->whereNull('doctor_id')
                         ->whereHas('examType', fn($eq) => $eq->where('specialty_id', $doctor->specialty_id));
                  })
                  ->orWhere(function($sq) {
                      $sq->whereNull('doctor_id')
                         ->where('type', 'custom');
                  });
            })
            ->where(function($sq) use ($myDoctorId) {
                $sq->where('doctor_id', $myDoctorId)
                    ->orWhereNull('doctor_id')
                    ->orWhere('claimed_at', '<', now()->subMinutes(20));
            });
    } else {
        // Pestaña Historial: Mostramos lo FIRMADO o lo RECHAZADO por el doctor
        $query->where('doctor_id', $myDoctorId)
              ->where(function($q) {
                  $q->whereNotNull('signed_at')
                    ->orWhere('status', 'rejected');
              });
    }

    return view('livewire.medical-orders-table', [
        'orders' => $query->latest('updated_at')->paginate(10) // Usamos updated_at para ver lo reciente arriba
    ]);
}




}
