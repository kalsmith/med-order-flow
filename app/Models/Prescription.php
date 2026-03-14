<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\DB;

class Prescription extends Model
{
    // 1. IMPORTANTE: Definir que el incremento es falso porque usas HasUuids
    use HasUuids, LogsActivity;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'doctor_id',
        'exam_type_id',
        'correlative_number',
        'status',
        'verification_code',
        'clinical_context',
        'void_reason',
        'signed_at'
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'correlative_number' => 'integer',
        'doctor_id' => 'integer', // Para evitar el error de tipos en comparaciones
    ];

    // Auditoría de Spatie mejorada
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'void_reason', 'signed_at', 'clinical_context']) // Añadimos contexto clínico
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs() // No crear logs si no hubo cambios reales
            ->setDescriptionForEvent(fn(string $eventName) => "Documento médico {$eventName}");
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // 2. CORRECCIÓN CORRELATIVO: Usar lockForUpdate o asegurar que no sea null
            // Si la tabla está vacía, max() devuelve null
            $max = DB::table('prescriptions')->max('correlative_number');
            $model->correlative_number = ($max && $max >= 1000) ? $max + 1 : 1001;

            if (!$model->verification_code) {
                $model->verification_code = self::generateUniqueVerificationCode();
            }
        });
    }

    public static function generateUniqueVerificationCode()
    {
        // Genera un código de 8 caracteres alfanuméricos más limpio
        do {
            $code = strtoupper(bin2hex(random_bytes(4))); // 8 caracteres hex
        } while (self::where('verification_code', $code)->exists());

        return $code;
    }

    // RELACIONES
    public function order(): BelongsTo
    {
        // Asegúrate de que la relación use el nombre correcto de la columna
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class, 'exam_type_id');
    }

    // SCOPES ÚTILES
    public function scopeSigned($query)
    {
        return $query->where('status', 'signed');
    }
}
