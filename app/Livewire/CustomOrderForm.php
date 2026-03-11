<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Patient;
use App\Models\ExamType;
use Illuminate\Support\Facades\Auth;

// EL NOMBRE DE LA CLASE DEBE SER IGUAL AL NOMBRE DEL ARCHIVO
class CustomOrderForm extends Component
{
    public $exam;
    public $patients;
    public $selected_patient_id;
    public $showAddFamily = false;

    // --- PROPIEDADES PARA EL FORMULARIO ---
    public $new_full_name;
    public $new_rut;
    public $new_relationship;
    public $new_birth_date;
    public $new_gender_biologic = 'M';

    // Agregamos estas por si tu vista las usa (según el blade anterior)
    public $custom_exam_name;
    public $symptoms;
    public $patient_type = 'adulto';
    public $urgency = 'normal';

    public function mount($examId = null) // Lo hacemos opcional por si la ruta no lo trae
    {
        if ($examId) {
            $this->exam = ExamType::find($examId);
        }

        $this->loadPatients();

        $primary = $this->patients->where('relationship', 'self')->first();
        if ($primary) {
            $this->selected_patient_id = $primary->id;
        }
    }

    public function loadPatients()
    {
        $this->patients = Auth::user()->patients()->get() ?? collect();
    }

    public function selectPatient($id)
    {
        $this->selected_patient_id = $id;
        $this->showAddFamily = false;
    }

    public function toggleAddFamily()
    {
        $this->showAddFamily = !$this->showAddFamily;
        if ($this->showAddFamily) {
            $this->selected_patient_id = null;
        }
    }

    public function saveFamily()
    {
        $this->validate([
            'new_full_name'       => 'required|string|min:3',
            'new_rut'             => 'required|string|min:7',
            'new_relationship'    => 'required|in:hijo,conyuge,padre,otro',
            'new_birth_date'      => 'required|date',
            'new_gender_biologic' => 'required|in:M,F',
        ]);

        $cleanRut = preg_replace('/[^kK0-9]/', '', $this->new_rut);

        $patient = Auth::user()->patients()->create([
            'full_name'       => $this->new_full_name,
            'rut'             => $cleanRut,
            'relationship'    => $this->new_relationship,
            'birth_date'      => $this->new_birth_date,
            'gender_biologic' => $this->new_gender_biologic,
            'is_primary'      => 0,
            'prevision'       => 'Particular',
        ]);

        $this->reset([
            'new_full_name',
            'new_rut',
            'new_relationship',
            'new_birth_date',
            'new_gender_biologic',
            'showAddFamily'
        ]);

        $this->loadPatients();
        $this->selected_patient_id = $patient->id;
    }

    public function render()
    {
        // Asegúrate de que el archivo blade se llame así:
        // resources/views/livewire/custom-order-form.blade.php
        return view('livewire.custom-order-form');
    }
}
