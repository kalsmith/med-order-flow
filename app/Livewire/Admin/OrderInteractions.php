<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Order; // <--- Cambiado de MedicalOrder a Order

class OrderInteractions extends Component
{
    public Order $order; // <--- Cambiado el type-hinting a Order
    public $message = '';
    public $lastMessageCount = 0;

    public function mount()
    {
        // Al ser un modelo de Eloquent, Livewire lo inyecta automáticamente si el nombre coincide
        $this->lastMessageCount = $this->order->interactions()->count();
    }

    public function refreshMessages()
    {
        $this->order->load('interactions');
        $currentCount = $this->order->interactions->count();

        if ($currentCount > $this->lastMessageCount) {
            $this->dispatch('new-messages-received');
            $this->lastMessageCount = $currentCount;
        }
    }

    public function sendMessage()
    {
        if (empty(trim($this->message))) return;

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
