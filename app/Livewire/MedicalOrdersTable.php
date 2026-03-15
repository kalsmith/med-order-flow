<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class MedicalOrdersTable extends Component
{
    use WithPagination;

    public $tab = 'available'; // available, reentry, signed

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

        $query = Order::with(['patient' => fn($q) => $q->withTrashed(), 'examType', 'doctor.user', 'activePrescription', 'prescriptions'])->withCount('interactions');

        if ($this->tab === 'available') {
            // Órdenes pagadas, no firmadas Y QUE NUNCA han tenido una prescripción (Nuevas puras)
            $query->where('status', 'paid')
                ->whereNull('signed_at')
                ->whereDoesntHave('prescriptions')
                ->where(function($q) use ($myDoctorId, $doctor) {
                    $q->where('doctor_id', $myDoctorId)
                    ->orWhereNull('doctor_id');
                });

        } elseif ($this->tab === 'reentry') {
            // Órdenes que tienen al menos una anulada y NO están firmadas actualmente
            // (OJO: En tu DB el signed_at tiene valor, por eso no la ves. La query debe ignorar el signed_at si hay una voided reciente)
            $query->where('status', 'paid')
                ->whereHas('prescriptions', fn($q) => $q->where('status', 'voided'))
                ->where(function($q) {
                    $q->whereNull('signed_at')
                    ->orWhereHas('prescriptions', fn($sq) => $sq->where('status', 'active')->whereNull('signed_at'));
                });

        } else {
            // Historial: Solo lo que YO firmé o lo que fue rechazado/reembolsado
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
