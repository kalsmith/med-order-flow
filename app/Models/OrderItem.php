<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'exam_type_id',
        'exam_name',
        'status',
    ];

    // Relación inversa: Un ítem pertenece a una orden
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Relación con el catálogo: Para sacar códigos Fonasa o descripciones
    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }
}
