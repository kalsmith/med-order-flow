<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\Order; // <--- Cambiado a Order

class OrderChat extends Component
{
    public Order $order; // <--- Cambiado a Order
    public $newMessage = '';
    public $lastMessageCount = 0;

    public function mount()
    {
        $this->lastMessageCount = $this->order->interactions()->count();
    }

    public function refreshMessages()
    {
        $this->order->load('interactions');
        $currentCount = $this->order->interactions->count();

        if ($currentCount > $this->lastMessageCount) {
            $lastMessage = $this->order->interactions->last();

            if($lastMessage && $lastMessage->sender_type === 'doctor') {
                $this->dispatch('refresh-order-item');
            }

            $this->dispatch('new-messages-received');
            $this->lastMessageCount = $currentCount;
        }
    }

    public function sendMessage()
    {
        if (empty(trim($this->newMessage))) return;

        $this->validate(['newMessage' => 'required|string|max:1000']);

        // Eloquent usará automáticamente 'order_id' gracias a la relación definida en el modelo Order
        $this->order->interactions()->create([
            'sender_type' => 'patient',
            'type'        => 'text',
            'content'     => $this->newMessage,
        ]);

        $this->newMessage = '';
        $this->order->load('interactions');
        $this->lastMessageCount = $this->order->interactions->count();

        $this->dispatch('scroll-bottom');
    }

    public function render()
    {
        $messages = $this->order->interactions()->orderBy('created_at', 'asc')->get();

        // Lógica de negocio: El paciente responde si hay previo del doctor
        $canPatientReply = $messages->where('sender_type', 'doctor')->count() > 0;

        return view('livewire.patient.order-chat', [
            'messages' => $messages,
            'canPatientReply' => $canPatientReply
        ]);
    }
}
