<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Patient;
use App\Models\ExamType;
use Illuminate\Support\Facades\Auth;

class OrderCheckout extends Component
{
    public $exam;
    public $patients;
    public $selected_patient_id;
    public $showAddFamily = false;

    // --- PROPIEDADES PARA EL FORMULARIO (Sincronizadas con wire:model) ---
    public $new_full_name;
    public $new_rut;
    public $new_relationship;
    public $new_birth_date;
    public $new_gender_biologic = 'M'; // Default Masculino para evitar nulos

    public function mount($examId)
    {
        $this->exam = ExamType::find($examId);
        $this->loadPatients();

        // Seleccionar por defecto al titular (self)
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
        // 1. Validación estricta con los nombres de las propiedades públicas
        $this->validate([
            'new_full_name'       => 'required|string|min:3',
            'new_rut'             => 'required|string|min:7',
            'new_relationship'    => 'required|in:hijo,conyuge,padre,otro',
            'new_birth_date'      => 'required|date',
            'new_gender_biologic' => 'required|in:M,F',
        ]);

        // 2. Limpieza de RUT para la base de datos
        $cleanRut = preg_replace('/[^kK0-9]/', '', $this->new_rut);

        // 3. Creación usando el esquema exacto de tu tabla SQL
        $patient = Auth::user()->patients()->create([
            'full_name'       => $this->new_full_name,
            'rut'             => $cleanRut,
            'relationship'    => $this->new_relationship,
            'birth_date'      => $this->new_birth_date,
            'gender_biologic' => $this->new_gender_biologic,
            'is_primary'      => 0,
            'prevision'       => 'Particular', // Valor por defecto
        ]);

        // 4. Reset de variables y refresco de interfaz
        $this->reset([
            'new_full_name',
            'new_rut',
            'new_relationship',
            'new_birth_date',
            'new_gender_biologic',
            'showAddFamily'
        ]);

        $this->loadPatients();

        // 5. Seleccionar automáticamente al familiar recién creado
        $this->selected_patient_id = $patient->id;
    }

    public function render()
    {
        return view('livewire.order-checkout');
    }
}
