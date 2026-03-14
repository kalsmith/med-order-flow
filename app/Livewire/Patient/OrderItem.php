<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\Order;

class OrderItem extends Component
{
    public $order;

    public function mount(Order $order)
    {
        // Cargamos las relaciones para evitar consultas extra en el loop
        $this->order = $order->loadMissing(['examType', 'activePrescription']);
    }

    public function render()
    {
        return view('livewire.patient.order-item');
    }
}
