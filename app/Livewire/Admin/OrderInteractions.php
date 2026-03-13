<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\MedicalOrder;

class OrderInteractions extends Component
{
    public MedicalOrder $order;
    public $message = '';

    protected $rules = [
        'message' => 'required|string|min:5|max:1000',
    ];

    public function sendMessage()
    {
        $this->validate();

        $this->order->interactions()->create([
            'user_id' => auth()->id(),
            'content' => $this->message,
            'sender_type' => 'doctor',
        ]);

        // Opcional: Cambiar estado a "esperando respuesta"
        // $this->order->update(['status' => 'pending_info']);

        $this->message = '';
        $this->dispatch('message-sent'); // Para notificaciones frontend
    }

    public function render()
    {
        return view('livewire.admin.order-interactions');
    }
}
