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
    /**
     * HasUuids: Fundamental para que Livewire reconozca el ID char(36).
     * LogsActivity: Para auditoría de cambios.
     */
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
        'custom_description',
        'clinical_context',
    ];

    /**
     * El casting a 'datetime' de created_at evita el error "format() on null"
     * al asegurar que siempre sea un objeto Carbon.
     */
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

    /**
     * Relación con las prescripciones (documentos médicos generados)
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'order_id');
    }

    /**
     * Relación con el chat/mensajería.
     * Usamos el modelo MedicalOrderInteraction definido anteriormente.
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(MedicalOrderInteraction::class, 'order_id');
    }

    /**
     * Obtiene la prescripción activa/firmada más reciente.
     */
    public function activePrescription(): HasOne
    {
        return $this->hasOne(Prescription::class, 'order_id')
            ->whereIn('status', ['signed', 'active'])
            ->latestOfMany();
    }

    // --- ACCESSORS / LÓGICA DE PRESENTACIÓN ---

    /**
     * Retorna el nombre legible de lo solicitado.
     */
    public function getDisplayNameAttribute()
    {
        if ($this->type === 'custom' && !empty($this->custom_description)) {
            return $this->custom_description;
        }
        return $this->examType ? $this->examType->name : 'Consulta Médica General';
    }




    // En App\Models\Order.php

public function scopeAvailableForDoctor($query, $doctorId, $specialtyId)
{
    return $query->where('status', 'paid')
        ->where(function($q) use ($doctorId, $specialtyId) {
            $q->whereHas('examType', fn($e) => $e->where('specialty_id', $specialtyId))
              ->orWhere('type', 'custom');
        })
        // No debe tener NINGUNA receta firmada (signed)
        ->whereDoesntHave('prescriptions', fn($p) => $p->where('status', 'signed'))
        // No debe tener NINGUNA receta anulada (si tiene anuladas, va a la otra pestaña)
        ->whereDoesntHave('prescriptions', fn($p) => $p->where('status', 'voided'));
}

public function scopeNeedsReentry($query)
{
    // Una orden necesita re-firma si tiene alguna anulada (voided)
    // PERO la receta más reciente aún no está firmada.
    return $query->where('status', 'paid')
        ->whereHas('prescriptions', fn($p) => $p->where('status', 'voided'))
        ->whereDoesntHave('prescriptions', fn($p) => $p->where('status', 'signed'));
}

public function scopeInHistory($query)
{
    return $query->where(function($q) {
        $q->whereHas('prescriptions', fn($p) => $p->where('status', 'signed'))
          ->orWhereIn('status', ['rejected', 'refund_pending', 'refunded']);
    });
}






}
