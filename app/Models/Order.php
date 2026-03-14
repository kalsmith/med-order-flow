<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Order extends Model
{
    use HasUuids, LogsActivity;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'patient_id',
        'doctor_id',           // Para el sistema de asignación
        'exam_type_id',
        'amount',
        'status',
        'type',                // 'standard' o 'custom'
        'flow_order_id',
        'flow_refund_id',
        'claimed_at',          // Bloqueo temporal de 20 min para médicos
        'custom_description',  // Lo que el paciente solicita en 'custom'
        'rejection_reason',    // Por si el médico rechaza la solicitud
        'verification_code',   // Para el QR del PDF final
    ];

    protected $casts = [
        'amount' => 'integer',
        'claimed_at' => 'datetime',
    ];

    /**
     * Boot del modelo: Generación automática del código de verificación.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->status) {
                $model->status = 'pending';
            }

            if (!$model->verification_code) {
                $model->verification_code = self::generateUniqueVerificationCode();
            }
        });
    }

    /**
     * Configuración de Spatie para auditar cambios.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'amount', 'doctor_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "La orden ha sido {$eventName}");
    }

    // --- RELACIONES ---

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function activePrescription(): HasOne
    {
        return $this->hasOne(Prescription::class)->where('status', 'active');
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(MedicalOrderInteraction::class, 'order_id')
                    ->orderBy('created_at', 'asc');
    }

    // --- LÓGICA DE NEGOCIO ---

    /**
     * Genera un código único de 8 caracteres hexadecimales para validación QR.
     */
    public static function generateUniqueVerificationCode()
    {
        do {
            $code = strtoupper(bin2hex(random_bytes(4)));
        } while (self::where('verification_code', $code)->exists());

        return $code;
    }

    /**
     * Nombre descriptivo de la orden para el Admin y PDF.
     */
    public function getDisplayNameAttribute()
    {
        if ($this->type === 'custom' && !empty($this->custom_description)) {
            return $this->custom_description;
        }

        return $this->examType ? $this->examType->name : 'Consulta Médica General';
    }

    /**
     * Facilita la obtención de componentes si el examen es un pack.
     */
    public function getExamComponentsAttribute()
    {
        return $this->examType ? $this->examType->children : collect();
    }
}
