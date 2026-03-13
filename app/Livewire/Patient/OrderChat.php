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

    // Sin cambios mayores en la lógica, pero asegúrate de que el render
    // pase la información necesaria para validar el estado.

    public function render()
    {
        $messages = $this->order->interactions()->orderBy('created_at', 'asc')->get();

        // Verificamos si el doctor ha enviado al menos un mensaje
        $canPatientReply = $messages->where('sender_type', 'doctor')->count() > 0;

        return view('livewire.patient.order-chat', [
            'messages' => $messages,
            'canPatientReply' => $canPatientReply
        ]);
    }
}
