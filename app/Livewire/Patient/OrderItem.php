<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\Order; // <--- Cambiado

class OrderItem extends Component
{
    public Order $order; // <--- Cambiado
    public $userMarkedAsRead = false;

    protected $listeners = ['refresh-order-item' => 'handleNewMessage'];

    public function mount(Order $order)
    {
        $this->order = $order;
    }

    public function handleNewMessage()
    {
        $this->userMarkedAsRead = false;
        $this->order->load('interactions');
    }

    public function markAsRead()
    {
        $this->userMarkedAsRead = true;
    }

    public function render()
    {
        // El badge ahora depende de interacciones ligadas a la Order
        $hasDoctorMessages = $this->order->interactions()
            ->where('sender_type', 'doctor')
            ->exists();

        $showNotificationBadge = !$this->userMarkedAsRead && $hasDoctorMessages;

        return view('livewire.patient.order-item', [
            'showNotificationBadge' => $showNotificationBadge,
            'activePrescription' => $this->order->activePrescription // Cargamos la receta
        ]);
    }
}
