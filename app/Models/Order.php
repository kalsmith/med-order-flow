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
        'doctor_id',           // Solo para bloqueo (lock)
        'exam_type_id',
        'amount',
        'status',
        'type',
        'flow_order_id',
        'flow_refund_id',
        'claimed_at',
        'custom_description',
        'clinical_context',    // Contexto inicial del paciente
        // 'rejection_reason',  <-- Esto ahora debería vivir en Prescriptions o Interactions
        // 'verification_code', <-- ELIMINADO: Ahora es de la tabla Prescriptions
    ];

    protected $casts = [
        'amount' => 'integer',
        'claimed_at' => 'datetime',
        'created_at' => 'datetime', // Fuerza a que siempre sea un objeto Carbon
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->status) $model->status = 'pending';

            // ELIMINADA toda la lógica de verification_code de aquí
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'doctor_id']) // Reducido a lo administrativo
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "La orden comercial ha sido {$eventName}");
    }

    // --- RELACIONES ---

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    /**
     * Relación con las prescripciones (historial clínico)
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'order_id');
    }

    /**
     * Trae la prescripción firmada o la más reciente
     */
    public function activePrescription(): HasOne
    {
        return $this->hasOne(Prescription::class, 'order_id')
            ->whereIn('status', ['signed', 'active'])
            ->latestOfMany();
    }

    // --- LÓGICA DE PRESENTACIÓN ---

    public function getDisplayNameAttribute()
    {
        if ($this->type === 'custom' && !empty($this->custom_description)) {
            return $this->custom_description;
        }
        return $this->examType ? $this->examType->name : 'Consulta Médica General';
    }

    public function interactions(): HasMany
    {
        // Usamos el nombre exacto de tu modelo de chat
        return $this->hasMany(MedicalOrderInteraction::class, 'order_id');
    }
}
