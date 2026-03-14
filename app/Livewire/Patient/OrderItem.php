<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\Order;

class OrderItem extends Component
{
    // Usamos el ID como propiedad de respaldo por si el modelo se deshidrata
    public $orderId;
    public Order $order;
    public $userMarkedAsRead = false;

    public function mount(Order $order)
    {
        $this->order = $order;
        $this->orderId = $order->id;
    }

    public function markAsRead()
    {
        $this->userMarkedAsRead = true;
    }

    public function render()
    {
        // SEGURIDAD: Si por el UUID el modelo llega vacío al render, lo re-vinculamos
        if (!$this->order->exists || empty($this->order->id)) {
            $this->order = Order::with(['patient', 'examType', 'activePrescription'])->find($this->orderId);
        }

        // Carga de relaciones necesarias para evitar el "Consultar Médico" por error
        $this->order->loadMissing(['activePrescription', 'interactions']);

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
