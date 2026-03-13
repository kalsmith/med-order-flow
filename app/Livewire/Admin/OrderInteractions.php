<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\MedicalOrder;

class OrderInteractions extends Component
{
    public MedicalOrder $order;
    public $message = '';
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
