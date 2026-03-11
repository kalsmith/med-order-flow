<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Patient;
use App\Models\ExamType;
use Illuminate\Support\Facades\Auth;

class OrderCheckout extends Component
{
    public $exam_type;
    public $patients;
    public $selected_patient_id;
    public $showAddFamily = false;

    // Campos obligatorios para la Orden Médica
    public $new_full_name;
    public $new_rut;
    public $new_relationship;
    public $new_birth_date;
    public $new_gender_biologic = 'M'; // Por defecto Masculino

    public function mount($examTypeId)
    {
        $this->exam_type = ExamType::findOrFail($examTypeId);
        $this->loadPatients();

        // Seleccionar al titular por defecto
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
        if ($this->showAddFamily) $this->selected_patient_id = null;
    }

public function saveFamily()
{
    $this->validate([
        'new_full_name'       => 'required|string|min:8',
        'new_rut'             => 'required|string|min:7', // QUITAMOS el unique
        'new_relationship'    => 'required|in:hijo,conyuge,padre,otro',
        'new_birth_date'      => 'required|date|before:today',
        'new_gender_biologic' => 'required|in:M,F',
    ]);

    try {
        $patient = Auth::user()->patients()->create([
            'full_name'       => $this->new_full_name,
            'rut'             => $this->new_rut, // Se limpia en el Modelo
            'relationship'    => $this->new_relationship,
            'birth_date'      => $this->new_birth_date,
            'gender_biologic' => $this->new_gender_biologic,
            'is_primary'      => false,
            'prevision'       => 'Particular',
        ]);

        $this->reset(['new_full_name', 'new_rut', 'new_relationship', 'new_birth_date', 'new_gender_biologic', 'showAddFamily']);
        $this->loadPatients();
        $this->selected_patient_id = $patient->id;

    } catch (\Exception $e) {
        \Log::error("Error al guardar familiar: " . $e->getMessage());
        // Esto te permitirá ver el error en la pantalla si algo falla
        $this->addError('new_rut', 'Error técnico: ' . $e->getMessage());
    }
}

    public function render()
    {
        return view('livewire.order-checkout');
    }
}
