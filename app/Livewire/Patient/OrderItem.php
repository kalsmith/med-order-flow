<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\Order;

class OrderItem extends Component
{
    // Quitamos el tipado estricto "Order" para evitar el error de asignación nula
    public $order;
    public $orderId;
    public $userMarkedAsRead = false;

    protected $listeners = ['refresh-order-item' => '$refresh'];

    public function mount($order)
    {
        // Guardamos el ID por separado para asegurar la re-hidratación
        $this->order = $order;
        $this->orderId = $order->id ?? null;
    }

    public function markAsRead()
    {
        $this->userMarkedAsRead = true;
    }

    public function render()
    {
        // Si la orden se perdió en el ciclo de vida, la recuperamos por ID
        if (!$this->order || !($this->order instanceof Order) || !$this->order->exists) {
            $this->order = Order::find($this->orderId);
        }

        // Si después de intentar recuperarla sigue siendo null, no renderizamos nada (o un error controlado)
        if (!$this->order) {
            return <<<'blade'
                <div></div>
            blade;
        }

        // Cargar relaciones necesarias
        $this->order->loadMissing(['activePrescription', 'interactions', 'patient', 'examType']);

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
