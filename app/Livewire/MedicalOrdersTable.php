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
        $doctor = $user->doctor;
        $myDoctorId = $doctor->id ?? null;

        // 1. Garbage Collector (Se mantiene igual, funciona bien)
        Order::whereIn('status', ['pending', 'paid'])
            ->whereNotNull('claimed_at')
            ->where('claimed_at', '<', now()->subMinutes(20))
            ->update([
                'doctor_id' => null,
                'claimed_at' => null
            ]);

        $query = Order::with(['patient', 'examType', 'doctor.user']);

        if ($this->tab === 'available') {
            $query->where('status', 'paid')
                  ->where(function($q) use ($myDoctorId, $doctor) {
                      // A. Lo que ya tomé yo
                      $q->where('doctor_id', $myDoctorId)
                        // B. O lo que nadie ha tomado pero es de MI especialidad
                        ->orWhere(function($sq) use ($doctor) {
                            $sq->whereNull('doctor_id')
                               ->whereHas('examType', function($eq) use ($doctor) {
                                   $eq->where('specialty_id', $doctor->specialty_id);
                               });
                        })
                        // C. O solicitudes especiales (custom) sin doctor
                        ->orWhere(function($sq) {
                            $sq->whereNull('doctor_id')
                               ->where('type', 'custom');
                        });
                  })
                  // Seguridad: Si alguien la tomó pero ya pasaron 20 min, vuelve a estar disponible
                  ->where(function($sq) use ($myDoctorId) {
                      $sq->where('doctor_id', $myDoctorId)
                        ->orWhereNull('doctor_id')
                        ->orWhere('claimed_at', '<', now()->subMinutes(20));
                  });
        } else {
            // Historial: Solo lo que yo firmé
            $query->where('status', 'signed')
                  ->where('doctor_id', $myDoctorId);
        }

        return view('livewire.medical-orders-table', [
            'orders' => $query->latest()->paginate(10)
        ]);
    }



}
