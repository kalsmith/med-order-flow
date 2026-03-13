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
        // Aseguramos que la relación esté cargada para contar correctamente
        $this->lastMessageCount = $this->order->interactions()->count();
    }

    public function refreshMessages()
    {
        // Forzamos la recarga de la relación para obtener lo último de la DB
        $this->order->load('interactions');
        $currentCount = $this->order->interactions->count();

        if ($currentCount > $this->lastMessageCount) {
            $lastMessage = $this->order->interactions->last();

            if($lastMessage && $lastMessage->sender_type === 'doctor') {
                /**
                 * IMPORTANTE: Despachamos el evento de forma global.
                 * El OrderItem que tenga este ID de orden reaccionará.
                 */
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

        $this->order->interactions()->create([
            'sender_type' => 'patient',
            'type'         => 'text',
            'content'      => $this->newMessage,
        ]);

        $this->newMessage = '';

        // Actualizamos el contador local para que el propio envío no dispare la notificación de "nuevo"
        $this->order->load('interactions');
        $this->lastMessageCount = $this->order->interactions->count();

        $this->dispatch('scroll-bottom');
    }

    public function render()
    {
        // Obtenemos los mensajes ordenados
        $messages = $this->order->interactions()->orderBy('created_at', 'asc')->get();

        // El paciente solo puede responder si el doctor ya inició la conversación
        $canPatientReply = $messages->where('sender_type', 'doctor')->count() > 0;

        return view('livewire.patient.order-chat', [
            'messages' => $messages,
            'canPatientReply' => $canPatientReply
        ]);
    }
}
