<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use App\Support\RutHelper;

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
        'rut'             => 'encrypted',
        'birth_date'      => 'encrypted:date',
        'gender_biologic' => 'encrypted',
        'phone'           => 'encrypted',
        'prevision'       => 'encrypted',
        'is_primary'      => 'boolean',
    ];

    /**
     * MUTADOR (Set): Se ejecuta al asignar $patient->rut = '12.345.678-k'
     * Limpia el dato ANTES de que el Cast 'encrypted' lo cifre.
     */
    public function setRutAttribute($value)
    {
        $this->attributes['rut'] = RutHelper::clean($value);
    }

    /**
     * ACCESOR (Get): Se ejecuta al leer $patient->rut
     * Laravel primero desencripta el dato y luego este método lo formatea.
     */
    public function getRutAttribute($value)
    {
        return $value ? RutHelper::format($value) : null;
    }

    /**
     * Edad automática
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
