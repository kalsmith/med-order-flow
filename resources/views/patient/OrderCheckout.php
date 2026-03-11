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

    // --- PROPIEDADES PARA EL FORMULARIO (Coinciden con wire:model) ---
    public $new_full_name;
    public $new_rut;
    public $new_relationship;
    public $new_birth_date;
    public $new_gender_biologic = 'M'; // Default Masculino

    public function mount($examId)
    {
        // Si usas ExamType para el ID que viene por la URL
        $this->exam = ExamType::find($examId);
        $this->loadPatients();

        // Seleccionar por defecto al titular
        $primary = $this->patients->where('relationship', 'self')->first();
        if ($primary) {
            $this->selected_patient_id = $primary->id;
        }
    }

    public function loadPatients()
    {
        // Obtenemos los pacientes del usuario autenticado
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
        // 1. Validación (Los nombres deben coincidir con las propiedades public arriba)
        $this->validate([
            'new_full_name'       => 'required|string|min:3',
            'new_rut'             => 'required|string|min:7',
            'new_relationship'    => 'required|in:hijo,conyuge,padre,otro',
            'new_birth_date'      => 'required|date',
            'new_gender_biologic' => 'required|in:M,F',
        ]);

        // 2. Limpieza de RUT
        $cleanRut = preg_replace('/[^kK0-9]/', '', $this->new_rut);

        // 3. Creación en la DB (Usando los nombres de columna de tu CREATE TABLE)
        $patient = Auth::user()->patients()->create([
            'full_name'       => $this->new_full_name,
            'rut'             => $cleanRut,
            'relationship'    => $this->new_relationship,
            'birth_date'      => $this->new_birth_date, // Se guarda como texto según tu tabla
            'gender_biologic' => $this->new_gender_biologic,
            'is_primary'      => 0,
            'prevision'       => 'Particular', // Valor por defecto para evitar error de null si no tiene default
        ]);

        // 4. Limpiar formulario y refrescar lista
        $this->reset([
            'new_full_name',
            'new_rut',
            'new_relationship',
            'new_birth_date',
            'new_gender_biologic',
            'showAddFamily'
        ]);

        $this->loadPatients();

        // 5. Seleccionar automáticamente al nuevo familiar
        $this->selected_patient_id = $patient->id;
    }

    public function render()
    {
        return view('livewire.order-checkout');
    }
}
