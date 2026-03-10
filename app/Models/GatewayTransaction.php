<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GatewayTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'gateway',
        'buy_order',
        'token',
        'amount',
        'status',          // 'pending', 'authorized', 'failed'
        'payable_id',      // ID de la MedicalOrder (UUID)
        'payable_type',    // App\Models\MedicalOrder
        'raw_response'     // Respuesta completa de Flow para auditoría
    ];

    /**
     * Casts de atributos para facilitar su uso.
     */
    protected $casts = [
        'raw_response' => 'array',
        'amount'       => 'decimal:2',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    // --- Relaciones ---

    /**
     * El usuario que realizó el intento de pago.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación Polimórfica: Permite que la transacción pertenezca a una MedicalOrder
     * o a cualquier otro modelo de cobro en el futuro.
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    // --- Scopes (Ayudantes de consulta) ---

    /**
     * Filtra solo transacciones exitosas.
     */
    public function scopeAuthorized($query)
    {
        return $query->where('status', 'authorized');
    }

    /**
     * Filtra transacciones por una orden de compra específica.
     */
    public function scopeByBuyOrder($query, $order)
    {
        return $query->where('buy_order', $order);
    }

    // --- Helpers ---

    /**
     * Verifica rápidamente si el pago fue aprobado.
     */
    public function isPaid(): bool
    {
        return $this->status === 'authorized';
    }
}
