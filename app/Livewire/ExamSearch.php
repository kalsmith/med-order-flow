<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ExamType;

class ExamSearch extends Component
{
    public $search = '';
    public $selectedExams = [];

    const MAX_EXAMS = 16;

    public function toggleExam($id, $name)
    {
        if (isset($this->selectedExams[$id])) {
            unset($this->selectedExams[$id]);
        } else {
            if (count($this->selectedExams) >= self::MAX_EXAMS) {
                $this->dispatch('limit-reached');
                return;
            }

            // Solo permitimos añadir a la lista múltiple si NO es un pack (opcional según tu lógica)
            $exam = ExamType::find($id);
            if ($exam && $exam->children->count() === 0) {
                $this->selectedExams[$id] = $name;
            }
        }
    }

    public function clearSelection()
    {
        $this->selectedExams = [];
    }

    /**
     * URL para el flujo de orden múltiple (Botón de barra flotante)
     */
    public function getOrderUrlProperty()
    {
        $ids = implode(',', array_keys($this->selectedExams));
        // Genera algo como: /solicitar/multiple?ids=1,2,3
        return route('order.flow', ['type' => 'multiple', 'ids' => $ids]);
    }

    public function render()
    {
        $results = [];

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
                ->limit(12) // Ajustado para que cuadre con la grilla de 4 o 3 columnas
                ->get();
        }

        return view('livewire.exam-search', [
            'exams' => $results,
            'orderUrl' => $this->order_url,
            'maxExams' => self::MAX_EXAMS,
        ]);
    }
}
