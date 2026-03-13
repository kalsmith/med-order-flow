<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\MedicalOrder;

class OrderChat extends Component
{
    public MedicalOrder $order;
    public $newMessage = '';
    public $lastMessageCount = 0; // Para comparar con el conteo anterior

    public function mount()
    {
        $this->lastMessageCount = $this->order->interactions->count();
    }

    public function refreshMessages()
    {
        $this->order->load('interactions');
        $currentCount = $this->order->interactions->count();

        // Si hay más mensajes de los que había antes, notificamos al JS
        if ($currentCount > $this->lastMessageCount) {
            $this->dispatch('new-messages-received');
            $this->lastMessageCount = $currentCount;
        }
    }

    public function sendMessage()
    {
        $this->validate(['newMessage' => 'required|string|max:1000']);

        $this->order->interactions()->create([
            'sender_type' => 'patient',
            'type'        => 'text',
            'content'     => $this->newMessage,
        ]);

        $this->newMessage = '';
        $this->refreshMessages();
        $this->dispatch('scroll-bottom');
    }

    public function render()
    {
        return view('livewire.patient.order-chat');
    }
}
