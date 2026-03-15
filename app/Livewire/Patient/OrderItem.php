<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\Order;

class OrderItem extends Component
{
    public Order $order;

    public function mount(Order $order)
    {
        // Cargamos relaciones e interacciones para las validaciones
        $this->order = $order->loadMissing(['examType', 'activePrescription', 'interactions']);
    }

// App\Livewire\Patient\OrderItem.php

    public function render()
    {
        $order = $this->order;
        $activePrescription = $order->activePrescription;

        // 1. ¿Está firmada?
        $isSigned = $activePrescription && $activePrescription->status === 'signed';

        // 2. ¿Está en proceso de reembolso o ya reembolsada?
        $isRefunded = in_array($order->status, ['refund_pending', 'refunded']);

        // 3. ¿El doctor ha iniciado contacto? (Solo para custom)
        $hasDoctorMessage = $order->interactions
            ->where('sender_type', 'doctor')
            ->isNotEmpty();

        return view('livewire.patient.order-item', [
            'isSigned'    => $isSigned,
            'isRefunded'  => $isRefunded,
            'canShowChat' => ($order->type === 'custom') && $hasDoctorMessage,
            'canDownload' => ($order->status === 'paid') && $isSigned,
            // Solo mostramos "Procesando" si está pagada, no está firmada y no es reembolso
            'isProcessing' => ($order->status === 'paid') && !$isSigned && !$isRefunded,
            // Solo mostramos "Esperando contacto" si es custom, no hay mensajes y no es reembolso
            'waitingContact' => ($order->type === 'custom') && !$hasDoctorMessage && !$isRefunded && !$isSigned
        ]);
    }
}
