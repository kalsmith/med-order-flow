<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutRequest extends Model
{
    use HasFactory;

    // ESTO ES LO QUE FALTA:
    protected $fillable = [
        'doctor_id',
        'amount',
        'status',
        'evidence_path',
        'paid_at',
        'admin_notes'
    ];

    // Para que paid_at sea tratado como fecha automáticamente
    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
