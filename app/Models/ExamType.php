<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // <--- Importante

class ExamType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'specialty_id',
        'name',
        'description', // El nuevo Slogan/Bajada SEO
        'code_fonasa',
        'base_price',
        'is_active',
        'post_id'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'is_active' => 'boolean',
        'base_price' => 'integer'
    ];

    /**
     * Un examen pertenece a una especialidad médica.
     */
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    /**
     * Relación con las órdenes médicas generadas.
     */
    public function medicalOrders(): HasMany
    {
        return $this->hasMany(MedicalOrder::class);
    }

    /**
     * Relación Many-to-Many recursiva:
     * Obtiene los exámenes que componen esta batería/pack.
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(
            ExamType::class,
            'exam_type_bundle',
            'parent_id',
            'child_id'
        )->withTimestamps();
    }

    /**
     * Relación Many-to-Many recursiva inversa:
     * Obtiene los perfiles o baterías que contienen a este examen.
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(
            ExamType::class,
            'exam_type_bundle',
            'child_id',
            'parent_id'
        )->withTimestamps();
    }

    /**
     * Determina si el examen es un pack/batería (tiene hijos).
     */
    public function isProfile(): bool
    {
        // Usamos count() o exists() para evitar cargar todos los modelos en memoria
        return $this->children()->exists();
    }

    /**
     * Obtiene el total de exámenes que componen la batería.
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->children()->count();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }


    public function getPacksAttribute()
    {
        return $this->parents;
    }

    public function blogPost()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }


    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

}
