<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute; // Importante
use Carbon\Carbon;
use App\Support\RutHelper;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'full_name', 'rut', 'birth_date', 'gender_biologic',
        'relationship', 'is_primary', 'phone', 'prevision'
    ];

    /**
     * Definición de Casts limpia
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'full_name' => 'encrypted',
            'gender_biologic' => 'encrypted',
            'phone' => 'encrypted',
            'prevision' => 'encrypted',
        ];
    }

    /**
     * ATRIBUTO RUT: Limpia, Encripta y Desencripta/Formatea
     */
    protected function rut(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? RutHelper::format(decrypt($value)) : null,
            set: fn ($value) => encrypt(RutHelper::clean($value)),
        );
    }

    /**
     * ATRIBUTO FECHA: Encripta y Desencripta como Carbon
     */
    protected function birthDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse(decrypt($value)) : null,
            set: fn ($value) => encrypt($value),
        );
    }

    /**
     * Edad automática
     */
    public function getAgeAttribute(): ?int
    {
        // Accedemos al valor ya desencriptado por el método anterior
        return $this->birth_date instanceof Carbon ? $this->birth_date->age : null;
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
