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

    protected $fillable = [
        'patient_id',
        'amount',
        'status',
        'flow_order_id',
        'flow_refund_id',
        'exam_type_id',
        'type',
    ];

    // Configuración de Spatie para auditar cambios comerciales
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'amount'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "La orden comercial ha sido {$eventName}");
    }

    // --- RELACIONES ---

    /**
     * Relación con el tipo de examen.
     */
    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    /**
     * Relación con el paciente dueño de la orden.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Una orden comercial puede tener varias prescripciones (por anulaciones/correcciones).
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * La prescripción actual/activa.
     */
    public function activePrescription(): HasOne
    {
        return $this->hasOne(Prescription::class)->where('status', 'active');
    }

    /**
     * Relación con las interacciones del chat.
     * Vinculado a través de 'order_id' según la nueva estructura de tabla.
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(MedicalOrderInteraction::class, 'order_id');
    }

    // --- CONFIGURACIÓN UUID ---

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }
}
