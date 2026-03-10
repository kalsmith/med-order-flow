<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',      // Nuevo: Cifrado
        'rut',            // Cifrado
        'birth_date',     // Cifrado (se maneja como Carbon)
        'gender_biologic', // Nuevo: Cifrado (M/F)
        'phone',          // Cifrado
        'prevision'       // Cifrado
    ];

    /**
     * Magia de Laravel: Cifrado automático de datos sensibles.
     * Los datos se guardan encriptados en la DB y se desencriptan al leerlos.
     */
    protected $casts = [
        'full_name'       => 'encrypted',
        'rut'             => 'encrypted',
        'birth_date'      => 'encrypted:date',
        'gender_biologic' => 'encrypted',
        'phone'           => 'encrypted',
        'prevision'       => 'encrypted',
    ];

    /**
     * Accesor para calcular la edad automáticamente.
     * Uso: $patient->age
     */
    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? Carbon::parse($this->birth_date)->age : null;
    }

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
