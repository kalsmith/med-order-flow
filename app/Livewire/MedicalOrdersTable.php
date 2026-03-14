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

        // 1. Garbage Collector: Liberamos órdenes bloqueadas (Movido aquí para que limpie al refrescar)
        Order::whereIn('status', ['pending', 'paid'])
            ->whereNotNull('claimed_at')
            ->where('claimed_at', '<', now()->subMinutes(20))
            ->update([
                'doctor_id' => null,
                'claimed_at' => null
            ]);

        $query = Order::with(['patient', 'examType', 'doctor.user']);

        if ($this->tab === 'available') {
            // Pestaña 1: Órdenes que este doctor puede tomar
            $query->where(function($q) use ($myDoctorId, $user) {
                // Órdenes que ya tiene tomadas
                $q->where('doctor_id', $myDoctorId)
                  ->where('status', 'paid')
                // O órdenes custom pagadas que nadie ha tomado
                ->orWhere(function($sq) {
                    $sq->whereNull('doctor_id')
                       ->where('type', 'custom')
                       ->where('status', 'paid');
                });
            });
        } else {
            // Pestaña 2: Historial de lo que ya firmó
            $query->where('status', 'signed')
                  ->where('doctor_id', $myDoctorId);
        }

        return view('livewire.medical-orders-table', [
            'orders' => $query->latest()->paginate(10)
        ]);
    }
}
