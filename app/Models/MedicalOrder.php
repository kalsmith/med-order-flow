<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Importante para el UUID

class MedicalOrder extends Model
{
    use HasFactory, HasUuids;

    /**
     * El tipo de ID es UUID (string).
     */
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'exam_type_id',
        'status'
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'amount' => 'integer',
    ];

    // --- RELACIONES ---

    /**
     * El paciente que recibe la orden.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * El doctor que emite la orden.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * El tipo de examen solicitado (FK a exam_types).
     */
    public function examType()
    {
        return $this->belongsTo(ExamType::class);
    }

    // --- SCOPES & LOGIC ---

    /**
     * Scope para filtrar órdenes por estado.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Generar un código de verificación único para la orden (ej: para el QR).
     */
    public static function generateUniqueVerificationCode()
    {
        do {
            $code = strtoupper(bin2hex(random_bytes(4))); // Genera algo como 7F3A2B11
        } while (self::where('verification_code', $code)->exists());

        return $code;
    }
}
