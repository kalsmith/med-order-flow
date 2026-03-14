<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\Order;

class OrderItem extends Component
{
    // IMPORTANTE: Si usas objetos de Eloquent,
    // asegúrate de que el modelo use HasUuids
    public Order $order;

    public $userMarkedAsRead = false;

    protected $listeners = ['refresh-order-item' => 'handleNewMessage'];

    // Forzamos que Livewire entienda qué campos debe persistir
    public function mount(Order $order)
    {
        $this->order = $order;
    }

    public function render()
    {
        // Si el render llega aquí y el ID está vacío, es que el modelo no se hidrató
        // Forzamos la carga de relaciones si el objeto existe
        if($this->order && $this->order->exists) {
            $this->order->loadMissing(['activePrescription', 'interactions', 'patient', 'examType']);
        }

        $activePrescription = $this->order->activePrescription;

        $hasDoctorMessages = $this->order->interactions
            ->where('sender_type', 'doctor')
            ->isNotEmpty();

        $showNotificationBadge = !$this->userMarkedAsRead && $hasDoctorMessages;

        return view('livewire.patient.order-item', [
            'showNotificationBadge' => $showNotificationBadge,
            'activePrescription' => $activePrescription
        ]);
    }
}
