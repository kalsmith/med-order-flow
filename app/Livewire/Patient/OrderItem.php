<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\Order;

class OrderItem extends Component
{
    public $order; // Sin tipado estricto para evitar errores de hidratación

    public function mount(Order $order)
    {
        $this->order = $order;
    }

    public function render()
    {
        return view('livewire.patient.order-item');
    }
}
