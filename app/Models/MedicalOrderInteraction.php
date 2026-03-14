<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalOrderInteraction extends Model
{
    use HasFactory, HasUuids;

    /**
     * Nombre de la tabla (opcional si sigue siendo el mismo,
     * pero bueno dejarlo explícito si el modelo no se llama igual).
     */
    protected $table = 'medical_order_interactions';

    protected $fillable = [
        'order_id',       // <--- Actualizado para coincidir con tu tabla
        'sender_type',
        'type',
        'content',
        'attachment_path'
    ];

    /**
     * Relación con la Orden (Comercial)
     */
    public function order(): BelongsTo
    {
        // Ahora apunta al nuevo modelo Order usando la nueva columna order_id
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Helper para saber si el mensaje fue enviado por un doctor
     */
    public function isFromDoctor(): bool
    {
        return $this->sender_type === 'doctor';
    }

    /**
     * Helper para saber si el mensaje fue enviado por el paciente
     */
    public function isFromPatient(): bool
    {
        return $this->sender_type === 'patient';
    }
}
