<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MedicalOrderInteraction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'medical_order_id',
        'sender_type',
        'type',
        'content',
        'attachment_path'
    ];

    public function order()
    {
        return $this->belongsTo(MedicalOrder::class, 'medical_order_id');
    }
}
