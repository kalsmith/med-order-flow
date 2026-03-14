<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\Order;

class OrderItem extends Component
{
    public Order $order;
    public $userMarkedAsRead = false;

    protected $listeners = ['refresh-order-item' => '$refresh'];

    public function mount(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Esta función se ejecuta en cada refresh de Livewire.
     * Si el modelo perdió sus datos, lo re-hidratamos usando el ID.
     */
    public function hydrate()
    {
        if (!$this->order->exists && !empty($this->order->id)) {
            $this->order = Order::find($this->order->id);
        }
    }

    public function markAsRead()
    {
        $this->userMarkedAsRead = true;
    }

    public function render()
    {
        // Aseguramos que las relaciones estén cargadas para evitar N+1 y errores de null
        if ($this->order && $this->order->exists) {
            $this->order->loadMissing(['activePrescription', 'interactions', 'patient', 'examType']);
        }

        $activePrescription = $this->order->activePrescription;

        // Contamos interacciones del doctor que sean nuevas o existan
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
