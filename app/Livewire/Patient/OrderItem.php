<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\Order;

class OrderItem extends Component
{
    public Order $order;

    // Listeners para refrescar si hay eventos globales (opcional)
    protected $listeners = ['orderUpdated' => '$refresh'];

    public function mount(Order $order)
    {
        $this->order = $order;
    }

    public function render()
    {
        // Refrescamos la instancia de la base de datos en cada renderizado
        // para detectar cambios realizados por el médico.
        $this->order->load(['examType', 'activePrescription', 'interactions']);

        $order = $this->order;
        $activePrescription = $order->activePrescription;

        $isSigned = $activePrescription && $activePrescription->status === 'signed';
        $isRefunded = in_array($order->status, ['refund_pending', 'refunded']);

        $hasDoctorMessage = $order->interactions
            ->where('sender_type', 'doctor')
            ->isNotEmpty();

        return view('livewire.patient.order-item', [
            'isSigned'       => $isSigned,
            'isRefunded'     => $isRefunded,
            'canShowChat'    => ($order->type === 'custom') && $hasDoctorMessage,
            'canDownload'    => ($order->status === 'paid') && $isSigned,
            'isProcessing'   => ($order->status === 'paid') && !$isSigned && !$isRefunded,
            'waitingContact' => ($order->type === 'custom') && !$hasDoctorMessage && !$isRefunded && !$isSigned
        ]);
    }
}
