<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PayoutRequest extends Model
{
    use LogsActivity;

    protected $fillable = [
        'doctor_id',
        'amount',
        'status',
        'evidence_path',
        'paid_at',
        'admin_notes'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'paid_at'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Solicitud de retiro {$eventName}");
    }

    // --- Relaciones ---

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
