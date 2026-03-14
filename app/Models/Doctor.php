<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prefix',
        'rut',
        'rnpi_number',
        'address',
        'signature_path',
        'is_active',
        'last_assigned_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_assigned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specialties()
    {
        return $this->belongsToMany(Specialty::class, 'doctor_specialty');
    }

    public function medicalOrders()
    {
        // Corregido: Apuntar al modelo Order
        return $this->hasMany(Order::class, 'doctor_id');
    }

    public function getNameAttribute()
    {
        return $this->user ? $this->prefix . ' ' . $this->user->name : 'Sin Nombre';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Motor Round Robin (Mantenlo igual, funciona bien)
    public static function getNextAvailableForSpecialty($specialtyId)
    {
        return self::where('is_active', true)
            ->whereHas('specialties', function($q) use ($specialtyId) {
                $q->where('specialties.id', $specialtyId);
            })
            ->orderByRaw('last_assigned_at IS NULL DESC')
            ->orderBy('last_assigned_at', 'asc')
            ->first();
    }
}

// <?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Storage;

// class Doctor extends Model
// {
//     use HasFactory;

//     /**
//      * Campos que se pueden asignar masivamente.
//      * Basado en tu migración: user_id, rut, rnpi_number, address, signature_path, is_active.
//      */
//     protected $fillable = [
//         'user_id',
//         'rut',
//         'rnpi_number',
//         'address',
//         'signature_path',
//         'is_active',
//         'last_assigned_at', // <-- Nuevo campo para el motor de rotación
//         'prefix'
//     ];

//     protected $casts = [
//         'is_active' => 'boolean',
//         'last_assigned_at' => 'datetime', // <-- Importante para comparaciones precisas
//     ];

//     // --- RELACIONES ---

//     /**
//      * Un Doctor pertenece a un Usuario (Cuenta de acceso).
//      */
//     public function user()
//     {
//         return $this->belongsTo(User::class);
//     }

//     /**
//      * Un Doctor puede tener muchas especialidades (Tabla pivote: doctor_specialty).
//      */
//     public function specialties()
//     {
//         return $this->belongsToMany(Specialty::class, 'doctor_specialty');
//     }

//     /**
//      * Un Doctor emite muchas órdenes médicas.
//      */
//     public function medicalOrders()
//     {
//         return $this->hasMany(MedicalOrder::class);
//     }

//     // --- ACCESORS & LOGIC ---

//     /**
//      * Obtener la URL de la firma digital.
//      * Útil si guardas la firma en el storage privado o público.
//      */
//     public function getSignatureUrlAttribute()
//     {
//         return $this->signature_path
//             ? Storage::url($this->signature_path)
//             : null;
//     }

//     /**
//      * Scope para filtrar solo doctores activos.
//      * Uso: Doctor::active()->get();
//      */
//     public function scopeActive($query)
//     {
//         return $query->where('is_active', true);
//     }

//     /**
//      * Helper para obtener el nombre directamente desde el usuario vinculado.
//      */
//     public function getNameAttribute()
//     {
//         return $this->user ? $this->user->name : 'Sin Nombre';
//     }

// public static function getNextAvailableForSpecialty($specialtyId)
// {
//     Log::info("--- INICIO DE ROTACIÓN ---");
//     Log::info("Buscando para Especialidad ID: {$specialtyId}");

//     // 1. Obtenemos la consulta base (Query Builder) para no repetir código
//     $query = self::where('is_active', true)
//         ->whereHas('specialties', function($q) use ($specialtyId) {
//             // Aseguramos que busque en la tabla pivote correctamente
//             $q->where('specialties.id', $specialtyId);
//         });

//     // 2. LOGS DE DIAGNÓSTICO (Solo para desarrollo, puedes comentarlos después)
//     $candidates = (clone $query)->get();
//     Log::info("Candidatos totales encontrados: " . $candidates->count());

//     foreach ($candidates as $c) {
//         Log::info("ID: {$c->id} | last_assigned_at: " . ($c->last_assigned_at ?? 'NUNCA (NULL)'));
//     }

//     // 3. EJECUCIÓN DEL MOTOR (Round Robin + Prioridad de Nuevos)
//     $winner = $query
//         // Prioridad 1: Los que tienen NULL (Doctores nuevos o que nunca han recibido nada)
//         ->orderByRaw('last_assigned_at IS NULL DESC')
//         // Prioridad 2: De los que ya han recibido, el que atendió hace más tiempo (el más antiguo)
//         ->orderBy('last_assigned_at', 'asc')
//         // Prioridad 3: Desempate por ID (El que se registró primero)
//         ->orderBy('id', 'asc')
//         ->first();

//     if ($winner) {
//         Log::info(">>> GANADOR SELECCIONADO: ID {$winner->id} (RUT: {$winner->rut})");
//     } else {
//         Log::error("!!! ERROR: No hay doctores activos vinculados a la especialidad {$specialtyId}");
//     }

//     Log::info("--- FIN DE ROTACIÓN ---");

//     return $winner;
// }

// }
