<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\MedicalOrder;

class OrderItem extends Component
{
    public MedicalOrder $order;

    /**
     * Propiedad para ocultar manualmente el globo cuando el usuario hace clic.
     * Se mantiene en false hasta que el usuario abre el chat.
     */
    public $userMarkedAsRead = false;

    /**
     * Listeners:
     * 'refresh-order-item' -> Al recibir este evento, se ejecuta handleNewMessage.
     * Esto permite que si llega un mensaje nuevo mientras el usuario está viendo la lista,
     * el punto rojo vuelva a aparecer aunque lo hubiera cerrado antes.
     */
    protected $listeners = ['refresh-order-item' => 'handleNewMessage'];

    public function mount(MedicalOrder $order)
    {
        $this->order = $order;
    }

    /**
     * Se ejecuta cuando llega un mensaje nuevo del doctor.
     * Forzamos que el badge se muestre de nuevo y refrescamos la data.
     */
    public function handleNewMessage()
    {
        $this->userMarkedAsRead = false; // Resetear para que el badge vuelva a aparecer
        $this->order->load('interactions'); // Recargar mensajes para que exists() detecte el nuevo
    }

    /**
     * Se activa al hacer clic en "Ver Mensajes" / "Consultar Médico".
     */
    public function markAsRead()
    {
        $this->userMarkedAsRead = true;
    }

    public function render()
    {
        /**
         * Lógica de visibilidad del Badge:
         * 1. Que el usuario NO lo haya marcado como leído en esta sesión de vista.
         * 2. Que existan mensajes del doctor en la base de datos.
         */
        $hasDoctorMessages = $this->order->interactions()
            ->where('sender_type', 'doctor')
            ->exists();

        $showNotificationBadge = !$this->userMarkedAsRead && $hasDoctorMessages;

        return view('livewire.patient.order-item', [
            'showNotificationBadge' => $showNotificationBadge
        ]);
    }
}
