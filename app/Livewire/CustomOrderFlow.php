<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Patient;

class CustomOrderFlow extends Component
{
    public $patient; // El paciente titular que viene desde el controlador

    public function mount($patient)
    {
        $this->patient = $patient;
    }

    public function render()
    {
        return view('livewire.custom-order-flow');
    }
}
