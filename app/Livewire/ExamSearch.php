<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ExamType;
use Illuminate\Support\Facades\Log; // Importante para que funcione Log

class ExamSearch extends Component
{
    public $search = '';

    public function render()
    {
        $results = [];

        if (strlen($this->search) > 2) {
            // Convertimos el término de búsqueda a minúsculas
            $term = '%' . strtolower($this->search) . '%';

            $results = ExamType::where('is_active', true)
                ->where(function ($query) use ($term) {
                    // Usamos whereRaw con LOWER para forzar la comparación en minúsculas
                    $query->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereHas('children', function ($subQuery) use ($term) {
                            $subQuery->whereRaw('LOWER(name) LIKE ?', [$term]);
                        });
                })
                ->with(['parents', 'children'])
                ->get();

            // Log para verificar qué está pasando internamente
            Log::info("Buscando (insensible): " . $term . " | Encontrados: " . $results->count());
        }

        return view('livewire.exam-search', [
            'exams' => $results
        ]);
    }
}
