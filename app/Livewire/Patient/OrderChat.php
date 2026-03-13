<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\MedicalOrder;

class OrderChat extends Component
{
    public MedicalOrder $order;
    public $newMessage = '';

    protected $rules = [
        'newMessage' => 'required|string|max:500',
    ];

    public function sendMessage()
    {
        $this->validate();

        $this->order->interactions()->create([
            'user_id' => auth()->id(),
            'message' => $this->newMessage,
            'sender_type' => 'patient', // Hardcoded porque este componente es SOLO para pacientes
        ]);

        $this->newMessage = '';
        $this->order->load('interactions');

        // Opcional: emitir evento para scroll o notificaciones
        $this->dispatch('messageSent');
    }

    public function render()
    {
        return view('livewire.patient.order-chat');
    }
}
