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

    /**
     * Método para añadir/quitar exámenes de la lista
     */
    public function toggleExam($id, $name)
    {
        if (isset($this->selectedExams[$id])) {
            unset($this->selectedExams[$id]);
        } else {
            // Solo permitimos añadir si no es un Pack (los packs tienen su propio botón directo)
            $exam = ExamType::find($id);
            if ($exam && $exam->children->count() === 0) {
                $this->selectedExams[$id] = $name;
            }
        }
    }

    /**
     * Limpia la selección actual
     */
    public function clearSelection()
    {
        $this->selectedExams = [];
    }

    /**
     * Genera la URL para el flujo de orden múltiple
     * Formato: /flow/multiple?ids=1,2,3
     */
    public function getOrderUrlProperty()
    {
        $ids = implode(',', array_keys($this->selectedExams));
        return route('order.flow', ['type' => 'multiple', 'ids' => $ids]);
    }

    public function render()
    {
        $results = [];

        // Buscamos solo si hay más de 2 caracteres
        if (strlen(trim($this->search)) > 2) {
            $term = '%' . strtolower(trim($this->search)) . '%';

            $results = ExamType::where('is_active', true)
                ->where(function ($query) use ($term) {
                    $query->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereHas('children', function ($subQuery) use ($term) {
                            $subQuery->whereRaw('LOWER(name) LIKE ?', [$term]);
                        });
                })
                ->with(['parents', 'children'])
                ->limit(15) // Limitar resultados mejora el rendimiento en vivo
                ->get();
        }

        return view('livewire.exam-search', [
            'exams' => $results,
            'orderUrl' => $this->order_url // Pasamos la URL generada a la vista
        ]);
    }
}
