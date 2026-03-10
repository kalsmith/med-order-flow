<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    use HasUuids, LogsActivity;

    protected $fillable = [
        'uuid',
        'reference_code', // TRX-XXXXXX
        'sender_id',      // Usuario que paga (Paciente)
        'receiver_id',    // Usuario que recibe (Doctor/Clinica) - Puede ser null al inicio
        'reference_id',   // ID de la MedicalOrder (UUID)
        'amount',         // Monto bruto
        'platform_fee',   // Comisión de la plataforma
        'type',           // 'medical_order', 'subscription', etc.
        'status',         // 'pending', 'completed', 'failed'
        'metadata'        // Datos extra (JSON)
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Lógica automática al crear una transacción
     */
    protected static function booted()
    {
        static::creating(function ($transaction) {
            // 1. Generar UUID si no existe
            if (empty($transaction->uuid)) {
                $transaction->uuid = (string) Str::uuid();
            }

            // 2. Generar Código de Referencia único (TRX-8J2K9L)
            if (empty($transaction->reference_code)) {
                do {
                    $code = 'TRX-' . strtoupper(Str::random(8));
                } while (self::where('reference_code', $code)->exists());

                $transaction->reference_code = $code;
            }
        });
    }

    /**
     * Configuración de Logs de Actividad (Spatie)
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'amount', 'reference_code', 'type'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Transaction {$eventName}");
    }

    // --- Relaciones ---

    /**
     * El usuario que origina el pago (Paciente)
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * El usuario que recibe el dinero (Doctor/Prestador)
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Relación con el objeto que originó el pago (MedicalOrder)
     * Usamos payable para mantener la flexibilidad polimórfica
     */
    public function medicalOrder(): BelongsTo
    {
        return $this->belongsTo(MedicalOrder::class, 'reference_id');
    }

    // --- Accessors ---

    /**
     * Monto que realmente le queda al destinatario
     * $transaction->net_amount
     */
    public function getNetAmountAttribute()
    {
        return $this->amount - $this->platform_fee;
    }

    // --- Helpers ---

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
