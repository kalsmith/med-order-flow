<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute; // <--- IMPORTANTE
use Carbon\Carbon;
use App\Support\RutHelper; // <--- El Helper que creamos antes

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'rut',
        'birth_date',
        'gender_biologic',
        'relationship',
        'is_primary',
        'phone',
        'prevision'
    ];

    protected $casts = [
        'full_name'       => 'encrypted',
        'rut'             => 'encrypted', // Se mantiene para que Laravel encripte/desencripte
        'birth_date'      => 'encrypted:date',
        'gender_biologic' => 'encrypted',
        'phone'           => 'encrypted',
        'prevision'       => 'encrypted',
        'is_primary'      => 'boolean',
    ];

    /**
     * UNIFICACIÓN DE RUT:
     * Este atributo se encarga de que el RUT siempre se guarde limpio
     * y se recupere formateado, sin importar el cifrado.
     */
    protected function rut(): Attribute
    {
        return Attribute::make(
            // Al leer (get): Lo sacamos de la DB y lo formateamos (ej: 12.345.678-K)
            get: fn ($value) => $value ? RutHelper::format($value) : null,

            // Al escribir (set): Lo limpiamos antes de guardarlo (ej: 12345678K)
            set: fn ($value) => $value ? RutHelper::clean($value) : null,
        );
    }

    /**
     * Accesor para calcular la edad automáticamente.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? Carbon::parse($this->birth_date)->age : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function medicalOrders(): HasMany
    {
        return $this->hasMany(MedicalOrder::class);
    }
}
