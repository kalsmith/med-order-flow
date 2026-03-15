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
        'doctor_id',
        'exam_type_id',
        'amount',
        'status',
        'type',
        'flow_order_id',
        'flow_refund_id',
        'claimed_at',
        'signed_at', // Asegúrate de que esté en fillable para actualizaciones
        'custom_description',
        'clinical_context',
    ];

    protected $casts = [
        'amount' => 'integer',
        'claimed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'signed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->status) $model->status = 'pending';
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'doctor_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
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

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'order_id');
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(MedicalOrderInteraction::class, 'order_id');
    }

    public function activePrescription(): HasOne
    {
        return $this->hasOne(Prescription::class, 'order_id')
            ->whereIn('status', ['signed', 'active'])
            ->latestOfMany();
    }

    // --- ACCESSORS ---

    public function getDisplayNameAttribute()
    {
        if ($this->type === 'custom' && !empty($this->custom_description)) {
            return $this->custom_description;
        }
        return $this->examType ? $this->examType->name : 'Consulta Médica General';
    }

    // --- SCOPES DE FILTRADO ---

    /**
     * Órdenes listas para ser tomadas por un médico.
     */
    public function scopeAvailableForDoctor($query, $doctorId, $specialtyId)
    {
        return $query->where('status', 'paid')
            ->where(function($q) use ($specialtyId) {
                $q->whereHas('examType', fn($e) => $e->where('specialty_id', $specialtyId))
                  ->orWhere('type', 'custom');
            })
            // No debe tener recetas firmadas ni anuladas
            ->whereDoesntHave('prescriptions', fn($p) => $p->whereIn('status', ['signed', 'voided']))
            // Si ya tiene fecha de firma, ya no está disponible
            ->whereNull('signed_at');
    }

    /**
     * Órdenes que tienen correcciones pendientes.
     */
    public function scopeNeedsReentry($query)
    {
        return $query->where('status', 'paid')
            ->whereHas('prescriptions', fn($p) => $p->where('status', 'voided'))
            ->whereDoesntHave('prescriptions', fn($p) => $p->where('status', 'signed'));
    }

    /**
     * Historial de órdenes finalizadas (Excluyendo Standard si se desea separar).
     */
    public function scopeInHistory($query)
    {
        return $query->where(function($q) {
            $q->whereHas('prescriptions', fn($p) => $p->where('status', 'signed'))
              ->orWhereNotNull('signed_at')
              ->orWhereIn('status', ['rejected', 'refund_pending', 'refunded']);
        });
    }

    /**
     * Órdenes del flujo estándar auto-firmadas.
     * Si no te aparece nada, prueba quitando el ->whereNotNull('signed_at')
     * dentro del closure de prescriptions.
     */
public function scopeAutoSignedStandard($query, $doctorId)
{
    return $query->where('type', 'standard')
        ->where('status', 'paid')
        ->whereHas('prescriptions', function($q) use ($doctorId) {
            $q->where('doctor_id', $doctorId)
              ->where('status', 'signed');
        });
}
}
