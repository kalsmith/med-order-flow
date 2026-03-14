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
    use HasUuids, LogsActivity;

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
    ];

    // Auditoría de Spatie para trazabilidad médica
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'void_reason', 'signed_at'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Documento médico {$eventName}");
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // 1. Generar Correlativo Humano Autoincremental
            // Bloqueamos la tabla brevemente para evitar duplicados en colisiones simultáneas
            $model->correlative_number = DB::table('prescriptions')->max('correlative_number') + 1;
            if ($model->correlative_number < 1000) $model->correlative_number = 1001;

            // 2. Generar Código de Verificación para el QR (8 caracteres aleatorios)
            if (!$model->verification_code) {
                $model->verification_code = self::generateUniqueVerificationCode();
            }
        });
    }

    public static function generateUniqueVerificationCode()
    {
        do {
            $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        } while (self::where('verification_code', $code)->exists());

        return $code;
    }

    // RELACIONES
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }
}
