<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderInteraction extends Model
{
    use HasUuids;

    protected $table = 'medical_order_interactions'; // Si decides no renombrar la tabla física aún

    protected $fillable = [
        'order_id', // <--- Actualizado
        'sender_type',
        'type',
        'content',
        'attachment_path'
    ];

    public function order(): BelongsTo
    {
        // Ahora apunta al nuevo modelo Order
        return $this->belongsTo(Order::class);
    }
}
