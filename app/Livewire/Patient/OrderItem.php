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

    public function render()
    {
        // 1. Lógica para el Chat
        $isCustom = $this->order->type === 'custom';
        $hasDoctorMessage = $this->order->interactions
            ->where('sender_type', 'doctor')
            ->isNotEmpty();

        // 2. Lógica para el Botón de Descarga
        // Verificamos si la receta activa tiene el status 'signed'
        $isSigned = $this->order->activePrescription && $this->order->activePrescription->status === 'signed';

        return view('livewire.patient.order-item', [
            'canShowChat' => $isCustom && $hasDoctorMessage,
            'canDownload' => $this->order->status === 'paid' && $isSigned
        ]);
    }
}
