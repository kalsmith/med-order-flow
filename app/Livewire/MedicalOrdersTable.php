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

        // 1. Garbage Collector: Liberar órdenes bloqueadas por inactividad
        Order::where('status', 'paid')
            ->whereNotNull('claimed_at')
            ->where('claimed_at', '<', now()->subMinutes(20))
            ->update([
                'doctor_id' => null,
                'claimed_at' => null
            ]);

        // 2. Query Base con relaciones necesarias
        $query = Order::with([
            'patient' => fn($q) => $q->withTrashed(),
            'examType',
            'doctor.user',
            'activePrescription',
            'prescriptions' // Cargamos historial para detectar anulaciones
        ])->withCount('interactions');

        if ($this->tab === 'available') {
            // Pestaña Pendientes: Sin fecha de firma y estado pagado
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
            // Pestaña Historial: Firmadas o con estados terminales (Rechazo/Reembolso)
            $query->where('doctor_id', $myDoctorId)
                  ->where(function($q) {
                      $q->whereNotNull('signed_at')
                        ->orWhereIn('status', ['rejected', 'refund_pending', 'refunded']);
                  });
        }

        return view('livewire.medical-orders-table', [
            'orders' => $query->latest('updated_at')->paginate(10)
        ]);
    }
}
