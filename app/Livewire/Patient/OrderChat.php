<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\Order;
use App\Models\MedicalOrderInteraction;

class OrderChat extends Component
{
    public Order $order;
    public $newMessage = '';
    public $lastMessageCount = 0;

    // Escuchamos eventos si es necesario, por ejemplo desde otros componentes
    protected $listeners = ['echo:orders,MessageSent' => 'refreshMessages'];

    public function mount(Order $order)
    {
        $this->order = $order;
        $this->lastMessageCount = $this->order->interactions()->count();
    }

    public function refreshMessages()
    {
        $currentCount = $this->order->interactions()->count();

        if ($currentCount > $this->lastMessageCount) {
            $this->order->load('interactions');
            $lastMessage = $this->order->interactions->last();

            // Si el doctor respondió, notificamos al componente padre (la lista)
            if($lastMessage && $lastMessage->sender_type === 'doctor') {
                $this->dispatch('refresh-order-item');
            }

            $this->dispatch('new-messages-received');
            $this->lastMessageCount = $currentCount;
            $this->dispatch('scroll-bottom');
        }
    }

    public function sendMessage()
    {
        $this->validate(['newMessage' => 'required|string|max:1000']);

        $this->order->interactions()->create([
            'sender_type' => 'patient',
            'type'        => 'message', // Ajustado a tu migración que usa 'message' por defecto
            'content'     => trim($this->newMessage),
        ]);

        $this->newMessage = '';
        $this->lastMessageCount++;

        $this->dispatch('scroll-bottom');
    }

    public function render()
    {
        $messages = $this->order->interactions()
            ->orderBy('created_at', 'asc')
            ->get();

        $canPatientReply = $messages->where('sender_type', 'doctor')->isNotEmpty();

        return view('livewire.patient.order-chat', [
            'messages' => $messages,
            'canPatientReply' => $canPatientReply
        ]);
    }
}
