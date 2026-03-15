<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Order;

class OrderInteractions extends Component
{
    public Order $order;
    public $message = '';
    public $lastMessageCount = 0;
    public $readOnly = false; // <-- Nueva propiedad

    public function mount($readOnly = false)
    {
        $this->readOnly = $readOnly;
        $this->lastMessageCount = $this->order->interactions()->count();
    }

    public function refreshMessages()
    {
        // Si está firmado, no tiene sentido seguir consultando por nuevos mensajes
        if ($this->readOnly) return;

        $this->order->load('interactions');
        $currentCount = $this->order->interactions->count();

        if ($currentCount > $this->lastMessageCount) {
            $this->dispatch('new-messages-received');
            $this->lastMessageCount = $currentCount;
        }
    }

    public function sendMessage()
    {
        // Bloqueo de seguridad en el servidor
        if ($this->readOnly || empty(trim($this->message))) return;

        $this->validate([
            'message' => 'required|string|max:1000',
        ]);

        $this->order->interactions()->create([
            'user_id' => auth()->id(),
            'content' => $this->message,
            'sender_type' => 'doctor',
            'type' => 'text'
        ]);

        $this->message = '';
        $this->refreshMessages();
        $this->dispatch('scroll-bottom');
    }

    public function render()
    {
        return view('livewire.admin.order-interactions', [
            'interactions' => $this->order->interactions()->orderBy('created_at', 'asc')->get()
        ]);
    }
}
