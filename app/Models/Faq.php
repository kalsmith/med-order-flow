<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     * Incluimos slug y category para que el controlador funcione correctamente.
     */
    protected $fillable = [
        'question',
        'slug',
        'answer',
        'category',
        'order',
        'is_active',
    ];

    /**
     * Casts para asegurar que los tipos de datos sean correctos al recuperarlos.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'order'     => 'integer',
    ];

    /**
     * Scope para filtrar solo los contenidos activos.
     * Uso: Faq::active()->get();
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para filtrar por categoría.
     * Uso: Faq::byCategory('faq')->get();
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
 * Indica a Laravel que use la columna 'slug' para el Implicit Model Binding.
 */
public function getRouteKeyName()
{
    return 'slug';
}

}
