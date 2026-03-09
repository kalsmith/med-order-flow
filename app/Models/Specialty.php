<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Specialty extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    // Boot method para generar el slug automáticamente si no viene
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($specialty) {
            if (empty($specialty->slug)) {
                $specialty->slug = Str::slug($specialty->name);
            }
        });
    }

    // Relación con Exámenes (según tu migración de exam_types)
    public function examTypes()
    {
        return $this->hasMany(ExamType::class);
    }
}
