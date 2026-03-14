<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\Order;

class OrderItem extends Component
{
    public Order $order;
    public $userMarkedAsRead = false;

    // Escucha si llega un nuevo mensaje para quitar el "leído" y forzar refresh
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
        // 1. Cargamos la relación de la prescripción activa (firmada o en proceso)
        $activePrescription = $this->order->activePrescription;

        // 2. Lógica del badge: mensajes del doctor que el usuario no ha "visto" al abrir el chat
        $hasDoctorMessages = $this->order->interactions()
            ->where('sender_type', 'doctor')
            ->exists();

        $showNotificationBadge = !$this->userMarkedAsRead && $hasDoctorMessages;

        return view('livewire.patient.order-item', [
            'showNotificationBadge' => $showNotificationBadge,
            'activePrescription' => $activePrescription
        ]);
    }
}
