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

// En app/Livewire/Patient/OrderChat.php

    public function refreshMessages()
    {
        $this->order->load('interactions');
        $currentCount = $this->order->interactions->count();

        if ($currentCount > $this->lastMessageCount) {
            // Buscamos si el nuevo mensaje es del doctor
            $lastMessage = $this->order->interactions->last();
            if($lastMessage->sender_type === 'doctor') {
                // Notificamos al componente OrderItem que hay algo nuevo
                $this->dispatch('refresh-order-item')->to(OrderItem::class);
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
