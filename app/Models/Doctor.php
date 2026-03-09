<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Doctor extends Model
{
    use HasFactory;

    /**
     * Campos que se pueden asignar masivamente.
     * Basado en tu migración: user_id, rut, rnpi_number, address, signature_path, is_active.
     */
    protected $fillable = [
        'user_id',
        'rut',
        'rnpi_number',
        'address',
        'signature_path',
        'is_active',
    ];

    /**
     * Casting de tipos para asegurar que is_active sea booleano.
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // --- RELACIONES ---

    /**
     * Un Doctor pertenece a un Usuario (Cuenta de acceso).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Un Doctor puede tener muchas especialidades (Tabla pivote: doctor_specialty).
     */
    public function specialties()
    {
        return $this->belongsToMany(Specialty::class, 'doctor_specialty');
    }

    /**
     * Un Doctor emite muchas órdenes médicas.
     */
    // public function medicalOrders()
    // {
    //     return $this->hasMany(MedicalOrder::class);
    // }

    // --- ACCESORS & LOGIC ---

    /**
     * Obtener la URL de la firma digital.
     * Útil si guardas la firma en el storage privado o público.
     */
    public function getSignatureUrlAttribute()
    {
        return $this->signature_path
            ? Storage::url($this->signature_path)
            : null;
    }

    /**
     * Scope para filtrar solo doctores activos.
     * Uso: Doctor::active()->get();
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Helper para obtener el nombre directamente desde el usuario vinculado.
     */
    public function getNameAttribute()
    {
        return $this->user ? $this->user->name : 'Sin Nombre';
    }
}
