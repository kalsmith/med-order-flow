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
        // 1. Condición: Tipo personalizado
        $isCustom = $this->order->type === 'custom';

        // 2. Condición: El médico ya escribió al menos una vez
        $hasDoctorMessage = $this->order->interactions
            ->where('sender_type', 'doctor')
            ->isNotEmpty();

        // Enviamos la variable a la vista
        return view('livewire.patient.order-item', [
            'canShowChat' => $isCustom && $hasDoctorMessage
        ]);
    }
}
