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

    /**
     * Dejamos en $casts solo lo que NO tiene mutadores personalizados
     * o lo que queremos que se transforme al salir (como boolean).
     */
    protected $casts = [
        'full_name'       => 'encrypted',
        'gender_biologic' => 'encrypted',
        'phone'           => 'encrypted',
        'prevision'       => 'encrypted',
        'is_primary'      => 'boolean',
    ];

    // --- MUTADORES (SET) ---

    /**
     * Limpia el RUT y lo encripta manualmente para asegurar el orden.
     */
    public function setRutAttribute($value)
    {
        $clean = RutHelper::clean($value);
        $this->attributes['rut'] = encrypt($clean);
    }

    /**
     * Asegura que la fecha se guarde siempre encriptada.
     */
    public function setBirthDateAttribute($value)
    {
        $this->attributes['birth_date'] = encrypt($value);
    }


    // --- ACCESORES (GET) ---

    /**
     * Desencripta y formatea el RUT para la vista.
     */
    public function getRutAttribute($value)
    {
        try {
            return $value ? RutHelper::format(decrypt($value)) : null;
        } catch (\Exception $e) {
            return $value; // Por si hay datos viejos sin encriptar
        }
    }

    /**
     * Desencripta la fecha y la retorna como objeto Carbon.
     */
    public function getBirthDateAttribute($value)
    {
        try {
            return $value ? Carbon::parse(decrypt($value)) : null;
        } catch (\Exception $e) {
            return $value ? Carbon::parse($value) : null;
        }
    }

    /**
     * Edad automática basada en el objeto Carbon de birth_date.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }


    // --- RELACIONES ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function medicalOrders(): HasMany
    {
        return $this->hasMany(MedicalOrder::class);
    }
}
