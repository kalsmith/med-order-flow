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
        'id',
        'patient_id',
        'doctor_id',
        'exam_type_id',
        'status',
        'type',               // <--- Nuevo
        'custom_description', // <--- Nuevo
        'clinical_context', // Agrégalo
        'rejection_reason', // Nuevo
        'internal_notes',   // Nuevo
        'flow_refund_id',   // Nuevo
        'amount',
        'verification_code',
        'signed_at',        // Agrégalo para poder actualizarlo manualmente
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
        // Definimos el nuevo estado según el tipo de orden
        $newStatus = ($this->type === 'standard') ? 'signed' : 'paid';

        $data = ['status' => $newStatus];

        // Si la orden es 'signed', marcamos la fecha
        if ($newStatus === 'signed') {
            $data['signed_at'] = now();
        }

        $this->update($data);

        Log::info("Orden {$this->id} actualizada a estado: {$newStatus}");
    }

    /**
     * Obtiene el nombre de la prestación de forma inteligente para el PDF
     */
    public function getDisplayNameAttribute()
    {
        // 1. Si el médico ya escribió algo en el contexto clínico (firma), eso manda.
        if (!empty($this->clinical_context)) {
            return $this->clinical_context;
        }

        // 2. Si es una orden personalizada y tiene descripción.
        if ($this->type === 'custom' && !empty($this->custom_description)) {
            return $this->custom_description;
        }

        // 3. Si es estándar, buscamos el nombre en la relación del examen.
        return $this->examType ? $this->examType->name : 'Consulta Médica General';
    }

    public function interactions()
    {
        return $this->hasMany(MedicalOrderInteraction::class)->orderBy('created_at', 'asc');
    }

    /**
     * Obtiene los componentes si el exam_type es un pack/perfil
     */
    public function getExamComponentsAttribute()
    {
        // Si no hay tipo de examen, devolvemos colección vacía
        if (!$this->examType) return collect();

        // Retornamos los hijos (componentes del pack)
        return $this->examType->children;
    }


}
