<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Order extends Model
{
    use HasUuids, LogsActivity;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'patient_id',
        'doctor_id',           // ID de la tabla 'doctors', NO user_id
        'exam_type_id',
        'amount',
        'status',
        'type',
        'flow_order_id',
        'flow_refund_id',
        'claimed_at',
        'custom_description',
        'clinical_context',    // <-- AGREGADO: Sin esto, la firma no guarda el texto
        'rejection_reason',
        'verification_code',
    ];

    protected $casts = [
        'amount' => 'integer',
        'claimed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->status) $model->status = 'pending';
            if (!$model->verification_code) {
                $model->verification_code = self::generateUniqueVerificationCode();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'doctor_id', 'clinical_context'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "La orden ha sido {$eventName}");
    }

    // --- RELACIONES CORREGIDAS ---

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        // Relación directa a la tabla doctors
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(MedicalOrderInteraction::class, 'order_id')->orderBy('created_at', 'asc');
    }

    // --- LÓGICA ---

    public static function generateUniqueVerificationCode()
    {
        do {
            $code = strtoupper(bin2hex(random_bytes(4)));
        } while (self::where('verification_code', $code)->exists());
        return $code;
    }

    public function getDisplayNameAttribute()
    {
        if ($this->type === 'custom' && !empty($this->custom_description)) {
            return $this->custom_description;
        }
        return $this->examType ? $this->examType->name : 'Consulta Médica General';
    }

    /**
     * Relación con las prescripciones (historial)
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'order_id');
    }

    /**
     * Relación con la prescripción actual/firmada (útil para el PDF)
     */
    public function prescription(): HasOne
    {
        return $this->hasOne(Prescription::class, 'order_id')->latestOfMany();
    }


}
