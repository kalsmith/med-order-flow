<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ExamType;
use Illuminate\Support\Facades\Log;

class ExamSearch extends Component
{
    public $search = '';
    // Guardamos un array de [id => name] para facilitar la gestión
    public $selectedExams = [];

    // Método para añadir/quitar exámenes de la lista
    public function toggleExam($id, $name)
    {
        if (isset($this->selectedExams[$id])) {
            unset($this->selectedExams[$id]);
        } else {
            $this->selectedExams[$id] = $name;
        }
    }

    // Método para limpiar todo
    public function clearSelection()
    {
        $this->selectedExams = [];
    }

    public function render()
    {
        $results = [];

        if (strlen($this->search) > 2) {
            $term = '%' . strtolower($this->search) . '%';

            $results = ExamType::where('is_active', true)
                ->where(function ($query) use ($term) {
                    $query->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereHas('children', function ($subQuery) use ($term) {
                            $subQuery->whereRaw('LOWER(name) LIKE ?', [$term]);
                        });
                })
                ->with(['parents', 'children'])
                ->get();

            Log::info("Buscando (insensible): " . $term . " | Encontrados: " . $results->count());
        }

        return view('livewire.exam-search', [
            'exams' => $results
        ]);
    }
}
