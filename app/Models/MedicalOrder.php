<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Log;

class MedicalOrder extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'patient_id',
        'doctor_id', // Será null al inicio
        'exam_type_id',
        'status',
        'amount',
        'verification_code',
        'pdf_path'
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'amount' => 'integer',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Forzamos que nazca en pending si no se especifica
            if (!$model->status) {
                $model->status = 'pending';
            }

            // Generamos el código para el QR automáticamente
            if (!$model->verification_code) {
                $model->verification_code = self::generateUniqueVerificationCode();
            }
        });
    }

    // --- RELACIONES ---

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class); // Puede ser null
    }

    public function examType()
    {
        return $this->belongsTo(ExamType::class);
    }

    // --- LÓGICA ---

    public static function generateUniqueVerificationCode()
    {
        do {
            $code = strtoupper(bin2hex(random_bytes(4)));
        } while (self::where('verification_code', $code)->exists());

        return $code;
    }

    /**
     * Obtener la transacción contable asociada a esta orden
     */
    public function paymentTransaction()
    {
        return $this->hasOne(Transaction::class, 'reference_id', 'id');
    }


// En app/Models/MedicalOrder.php


public function finalizePayment()
{
    // Lógica limpia y simple:
    if ($this->type === 'standard') {
        $this->update(['status' => 'signed', 'signed_at' => now()]);
    } else {
        $this->update(['status' => 'paid']);
    }
}


}
