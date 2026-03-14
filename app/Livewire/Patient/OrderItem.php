<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\Order;

class OrderItem extends Component
{
    public Order $order;
    public $userMarkedAsRead = false;

    // Escucha eventos globales (útil si el chat está en otro componente)
    protected $listeners = ['refresh-order-item' => 'handleNewMessage'];

    public function mount(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Se ejecuta cuando llega un nuevo mensaje.
     * Refresca la relación de interacciones para detectar el nuevo mensaje del doctor.
     */
    public function handleNewMessage()
    {
        $this->userMarkedAsRead = false;
        $this->order->load('interactions');
    }

    /**
     * Marca como leído localmente para ocultar el badge rojo.
     */
    public function markAsRead()
    {
        $this->userMarkedAsRead = true;
    }

    public function render()
    {
        // Cargamos relaciones necesarias si no vienen cargadas desde el OrderList
        // Esto evita el problema de N+1 consultas
        $this->order->loadMissing(['activePrescription', 'interactions', 'patient', 'examType']);

        $activePrescription = $this->order->activePrescription;

        // Lógica del badge:
        // 1. Que el usuario no haya hecho clic en "Ver Mensajes" en esta sesión ($userMarkedAsRead)
        // 2. Que exista al menos un mensaje donde el sender_type sea 'doctor'
        $hasDoctorMessages = $this->order->interactions
            ->where('sender_type', 'doctor')
            ->isNotEmpty();

        $showNotificationBadge = !$this->userMarkedAsRead && $hasDoctorMessages;

        return view('livewire.patient.order-item', [
            'showNotificationBadge' => $showNotificationBadge,
            'activePrescription' => $activePrescription
        ]);
    }
}
