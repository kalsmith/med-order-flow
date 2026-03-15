<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\Order;

class OrderItem extends Component
{
    public Order $order;

    protected $listeners = ['orderUpdated' => '$refresh'];

    public function mount(Order $order)
    {
        $this->order = $order;
    }

    public function render()
    {
        // Forzamos la carga de relaciones para evitar N+1 y tener datos frescos
        $this->order->load(['examType', 'activePrescription', 'interactions']);

        $order = $this->order;
        $activePrescription = $order->activePrescription;

        // 1. Estados base
        $isSigned = $activePrescription && $activePrescription->status === 'signed';
        $isRefunded = in_array($order->status, ['refund_pending', 'refunded']);
        $hasDoctorMessage = $order->interactions->where('sender_type', 'doctor')->isNotEmpty();

        return view('livewire.patient.order-item', [
            'isSigned'       => $isSigned,
            'isRefunded'     => $isRefunded,
            'canShowChat'    => ($order->type === 'custom') && $hasDoctorMessage && !$isSigned && !$isRefunded,
            'canDownload'    => ($order->status === 'paid') && $isSigned,
            'isProcessing'   => ($order->status === 'paid') && !$isSigned && !$isRefunded,
            'waitingContact' => ($order->type === 'custom') && !$hasDoctorMessage && !$isRefunded && !$isSigned
        ]);
    }
}
