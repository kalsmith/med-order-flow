<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id',
        'pack_id',
        'title',
        'slug',
        'summary',
        'content',
        'featured_image',
        'meta_title',
        'meta_keywords',
        'is_published',
        'published_at'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Relación con el Autor (User)
     * Para mostrar: "Escrito por Dr. Pérez"
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Relación con el Pack que promociona el artículo
     * Esta es la clave para la "Card de Conversión" al final del blog.
     */
    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class, 'cta_id');
    }

    public function hasProduct(): bool
    {
        return !is_null($this->cta_id) && in_array($this->cta_type, ['pack', 'exam']);
    }

    /**
     * SCOPE: Solo posts publicados
     * Uso: Post::published()->get();
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    /**
     * Accessor para la URL de la imagen
     * Si no hay imagen, devuelve un placeholder médico profesional.
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->featured_image && Storage::disk('public')->exists($this->featured_image)) {
            return Storage::url($this->featured_image);
        }

        return asset('assets/img/blog-placeholder.jpg');
    }

    /**
     * Route Key Name para SEO
     * Permite usar el slug en lugar del ID en las rutas automáticas.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
