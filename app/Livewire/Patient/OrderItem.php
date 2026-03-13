<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\MedicalOrder;

class OrderItem extends Component
{
    public MedicalOrder $order;

    // Propiedad para ocultar manualmente el globo cuando el usuario hace clic
    public $userMarkedAsRead = false;

    // Escucha el evento desde el componente OrderChat
    protected $listeners = ['refresh-order-item' => '$refresh'];

    public function mount(MedicalOrder $order)
    {
        $this->order = $order;
    }

    public function markAsRead()
    {
        $this->userMarkedAsRead = true;
    }

    public function render()
    {
        // Calculamos si debe mostrarse el globo en cada ciclo de renderizado (o poll)
        // Solo se muestra si NO ha sido marcado como leído manualmente Y hay mensajes del doctor
        $hasDoctorMessages = $this->order->interactions()->where('sender_type', 'doctor')->exists();

        $showNotificationBadge = !$this->userMarkedAsRead && $hasDoctorMessages;

        return view('livewire.patient.order-item', [
            'showNotificationBadge' => $showNotificationBadge
        ]);
    }
}
