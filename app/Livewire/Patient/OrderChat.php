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
        $this->validate([
            'newMessage' => 'required|string|max:1000'
        ]);

        // Usamos los nombres de columna correctos según tu modelo
        $this->order->interactions()->create([
            'sender_type' => 'patient',
            'type'        => 'text',      // Agregamos el tipo ya que está en tu fillable
            'content'     => $this->newMessage, // Cambiado de 'message' a 'content'
        ]);

        $this->newMessage = '';
        $this->order->load('interactions');
    }


    public function render()
    {
        return view('livewire.patient.order-chat');
    }
}
