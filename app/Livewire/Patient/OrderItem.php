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

// App\Livewire\Patient\OrderItem.php

    public function render()
    {
        $this->order->load(['examType', 'activePrescription', 'interactions']);

        $order = $this->order;
        $activePrescription = $order->activePrescription;

        // 1. ¿Está firmada?
        $isSigned = $activePrescription && $activePrescription->status === 'signed';

        // 2. ¿Está en proceso de reembolso o ya reembolsada?
        $isRefunded = in_array($order->status, ['refund_pending', 'refunded']);

        // 3. ¿El doctor ha iniciado contacto?
        $hasDoctorMessage = $order->interactions
            ->where('sender_type', 'doctor')
            ->isNotEmpty();

        return view('livewire.patient.order-item', [
            'isSigned'       => $isSigned,
            'isRefunded'     => $isRefunded,
            // ACTUALIZACIÓN: Solo mostramos chat si NO está firmada y NO es un reembolso
            'canShowChat'    => ($order->type === 'custom') && $hasDoctorMessage && !$isSigned && !$isRefunded,

            'canDownload'    => ($order->status === 'paid') && $isSigned,
            'isProcessing'   => ($order->status === 'paid') && !$isSigned && !$isRefunded,
            'waitingContact' => ($order->type === 'custom') && !$hasDoctorMessage && !$isRefunded && !$isSigned
        ]);
    }
}
