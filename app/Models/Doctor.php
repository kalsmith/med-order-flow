<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prefix',
        'rut',
        'rnpi_number',
        'address',
        'signature_path',
        'is_active',
        'last_assigned_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_assigned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specialties()
    {
        return $this->belongsToMany(Specialty::class, 'doctor_specialty');
    }

    public function medicalOrders()
    {
        // Corregido: Apuntar al modelo Order
        return $this->hasMany(Order::class, 'doctor_id');
    }

    public function getNameAttribute()
    {
        return $this->user ? $this->prefix . ' ' . $this->user->name : 'Sin Nombre';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Motor Round Robin (Mantenlo igual, funciona bien)
    public static function getNextAvailableForSpecialty($specialtyId)
    {
        return self::where('is_active', true)
            ->whereHas('specialties', function($q) use ($specialtyId) {
                $q->where('specialties.id', $specialtyId);
            })
            ->orderByRaw('last_assigned_at IS NULL DESC')
            ->orderBy('last_assigned_at', 'asc')
            ->first();
    }

    // En app/Models/Doctor.php

    /**
     * Recetas que el doctor ya firmó (lo que genera dinero)
     */


    /**
     * Historial de retiros
     */
    public function payoutRequests()
    {
        return $this->hasMany(PayoutRequest::class);
    }

/**
 * Recetas que el doctor ya firmó
 */
public function signedPrescriptions()
{
    // Especificamos 'prescriptions.status' para evitar ambigüedad
    return $this->hasMany(Prescription::class)->where('prescriptions.status', 'signed');
}

/**
 * CÁLCULO DE SALDO DISPONIBLE
 */
public function getBalanceAttribute()
{
    $totalEarned = $this->signedPrescriptions()
        ->join('orders', 'prescriptions.order_id', '=', 'orders.id')
        ->selectRaw("SUM(CASE WHEN orders.type = 'custom' THEN 2800 ELSE 1800 END) as total")
        // Agregamos explícitamente el filtro de status con tabla si no viene del signedPrescriptions
        ->where('prescriptions.status', 'signed')
        ->value('total') ?? 0;

    $totalPaid = $this->payoutRequests()->where('status', 'paid')->sum('amount');

    return $totalEarned - $totalPaid;
}
/**
 * Helper para obtener solo las que ya están firmadas (para contabilidad)
 */



}
