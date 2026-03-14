<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'exam_type_id', // <--- AGREGAR
        'type',         // <--- AGREGAR
    ];

    // Configuración de Spatie para auditar cambios comerciales
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'amount'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "La orden comercial ha sido {$eventName}");
    }

    // RELACIONES
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    // Una orden comercial puede tener varias prescripciones (por anulaciones/correcciones)
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    // La prescripción actual/activa
    public function activePrescription()
    {
        return $this->hasOne(Prescription::class)->where('status', 'active');
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

    // --- AGREGA ESTA RELACIÓN ---
    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

}
