<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rut',
        'birth_date',
        'phone',
        'prevision'
    ];

    /**
     * Un paciente está vinculado a un usuario (Auth).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Un paciente puede tener muchas órdenes médicas.
     */
    public function medicalOrders(): HasMany
    {
        return $this->hasMany(MedicalOrder::class);
    }
}
