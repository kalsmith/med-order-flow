<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\MedicalOrder;

class OrderChat extends Component
{
    public MedicalOrder $order;
    public $newMessage = '';
    public $lastMessageCount = 0;

    public function mount()
    {
        $this->lastMessageCount = $this->order->interactions()->count();
    }

    public function refreshMessages()
    {
        // Forzamos la recarga de la relación
        $this->order->load('interactions');
        $currentCount = $this->order->interactions->count();

        if ($currentCount > $this->lastMessageCount) {
            $this->dispatch('new-messages-received');
            $this->lastMessageCount = $currentCount;
        }
    }

    public function sendMessage()
    {
        if (empty(trim($this->newMessage))) return;

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
        return view('livewire.patient.order-chat', [
            // Pasamos los mensajes directamente para asegurar frescura
            'messages' => $this->order->interactions()->orderBy('created_at', 'asc')->get()
        ]);
    }
}
