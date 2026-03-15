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

        // 1. Garbage Collector
        Order::whereIn('status', ['pending', 'paid'])
            ->whereNotNull('claimed_at')
            ->where('claimed_at', '<', now()->subMinutes(20))
            ->update([
                'doctor_id' => null,
                'claimed_at' => null
            ]);

        // 2. Query Base con relaciones necesarias para la vista
        $query = Order::with([
            'patient' => fn($q) => $q->withTrashed(),
            'examType',
            'doctor.user',
            'activePrescription' // Importante para ver el estado de firma
        ])->withCount('interactions'); // Para el semáforo del chat

    if ($this->tab === 'available') {
        $query->where('status', 'paid')
            ->whereNull('signed_at') // <--- Agrega esto: Solo mostrar lo NO firmado
            ->where(function($q) use ($myDoctorId, $doctor) {
                    // A. Lo que ya tomé yo
                    $q->where('doctor_id', $myDoctorId)
                    // B. O lo que nadie ha tomado pero es de MI especialidad
                    ->orWhere(function($sq) use ($doctor) {
                        $sq->whereNull('doctor_id')
                           ->whereHas('examType', fn($eq) => $eq->where('specialty_id', $doctor->specialty_id));
                    })
                    // C. O solicitudes especiales (custom) sin doctor
                    ->orWhere(function($sq) {
                        $sq->whereNull('doctor_id')
                           ->where('type', 'custom');
                    });
                })
                // Seguridad: Si alguien la tomó pero expiró, está disponible
                ->where(function($sq) use ($myDoctorId) {
                    $sq->where('doctor_id', $myDoctorId)
                        ->orWhereNull('doctor_id')
                        ->orWhere('claimed_at', '<', now()->subMinutes(20));
                });
        } else {
            // Historial: Solo lo que yo ya firmé
            $query->where('status', 'signed')
                  ->where('doctor_id', $myDoctorId);
        }

        return view('livewire.medical-orders-table', [
            'orders' => $query->latest()->paginate(10)
        ]);
    }
}
