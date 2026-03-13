<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\MedicalOrder;

class OrderItem extends Component
{
    public MedicalOrder $order;

    public function render()
    {
        return view('livewire.patient.order-item');
    }
}
