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
        $term = '%' . $this->search . '%';

        $results = ExamType::where('is_active', true)
            ->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', $term) // Buscar por nombre del examen/pack
                      ->orWhereHas('children', function ($subQuery) use ($term) {
                          $subQuery->where('name', 'LIKE', $term); // O buscar por nombre de sus hijos
                      });
            })
            ->with(['parents', 'children'])
            ->get();

        // LOGS PARA VER LA MAGIA
        // Log::info("--- Nueva Búsqueda Inteligente ---");
        // Log::info("Término: " . $this->search);
        // Log::info("Resultados: " . $results->count());

        // foreach ($results as $exam) {
        //     Log::info("Encontrado: {$exam->name} (ID: {$exam->id})");
        // }
    }

    return view('livewire.exam-search', [
        'exams' => $results
    ]);
}
}
