<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Patient;
use App\Models\ExamType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderCheckout extends Component
{
    // Datos de los exámenes
    public $exam_type;          // Para flujo individual/pack
    public $selected_exams;     // Para flujo múltiple (colección de modelos)
    public $selected_exams_ids; // String de IDs para el formulario
    public $is_multiple = false;

    // Datos de pacientes
    public $patients;
    public $selected_patient_id;
    public $showAddFamily = false;

    // Campos para nuevo familiar
    public $new_full_name;
    public $new_rut;
    public $new_relationship;
    public $new_birth_date;
    public $new_gender_biologic = 'Masculino';

    /**
     * Ahora el mount acepta ambos casos.
     * Laravel Livewire empareja los parámetros automáticamente.
     */
    public function mount($examTypeId = null, $selectedExamsIds = null)
    {
        if ($selectedExamsIds) {
            // FLUJO BUSCADOR (Múltiple)
            $this->is_multiple = true;
            $this->selected_exams_ids = $selectedExamsIds;
            $ids = explode(',', $selectedExamsIds);
            $this->selected_exams = ExamType::whereIn('id', $ids)->get();
        } elseif ($examTypeId) {
            // FLUJO TRADICIONAL (Individual o Pack)
            $this->is_multiple = false;
            $this->exam_type = ExamType::findOrFail($examTypeId);
        }

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
            'new_rut'             => 'required|string|min:7',
            'new_relationship'    => 'required|in:hijo,conyuge,padre,otro',
            'new_birth_date'      => 'required|date|before:today',
            'new_gender_biologic' => 'required|in:Masculino,Femenino',
        ]);

        if (!\App\Support\RutHelper::validate($this->new_rut)) {
            $this->addError('new_rut', 'El RUT ingresado no es válido.');
            return;
        }

        try {
            $patient = Auth::user()->patients()->create([
                'full_name'       => $this->new_full_name,
                'rut'             => $this->new_rut,
                'relationship'    => $this->new_relationship,
                'birth_date'      => $this->new_birth_date,
                'gender_biologic' => $this->new_gender_biologic,
                'is_primary'      => false,
                'prevision'       => 'Particular',
            ]);

            $this->reset(['new_full_name', 'new_rut', 'new_relationship', 'new_birth_date', 'new_gender_biologic', 'showAddFamily']);
            $this->loadPatients();
            $this->selected_patient_id = $patient->id;

            session()->flash('message', 'Familiar añadido correctamente.');

        } catch (\Exception $e) {
            Log::error("Error al guardar familiar: " . $e->getMessage());
            $this->addError('new_rut', 'No se pudo guardar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.order-checkout');
    }
}
