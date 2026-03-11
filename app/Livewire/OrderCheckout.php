<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Patient;
use App\Models\ExamType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
    // 1. Validación básica de campos
    $this->validate([
        'new_full_name'       => 'required|string|min:8',
        'new_rut'             => 'required|string|min:7',
        'new_relationship'    => 'required|in:hijo,conyuge,padre,otro',
        'new_birth_date'      => 'required|date|before:today',
        'new_gender_biologic' => 'required|in:M,F',
    ]);

    // 2. Validación de Dígito Verificador (Helper)
    if (!\App\Support\RutHelper::validate($this->new_rut)) {
        $this->addError('new_rut', 'El RUT ingresado no es válido (Dígito verificador incorrecto).');
        return;
    }

    try {
        // Mapeo de género si prefieres guardar el nombre completo
        $genero = ($this->new_gender_biologic === 'M') ? 'Masculino' : 'Femenino';

        $patient = Auth::user()->patients()->create([
            'full_name'       => $this->new_full_name,
            'rut'             => $this->new_rut, // El Setter del modelo hará RutHelper::clean()
            'relationship'    => $this->new_relationship,
            'birth_date'      => $this->new_birth_date,
            'gender_biologic' => $genero,
            'is_primary'      => false,
            'prevision'       => 'Particular', // Valor por defecto para familiares
        ]);

        // Resetear campos y cerrar formulario
        $this->reset(['new_full_name', 'new_rut', 'new_relationship', 'new_birth_date', 'new_gender_biologic', 'showAddFamily']);

        // Recargar lista y seleccionar al nuevo paciente
        $this->loadPatients();
        $this->selected_patient_id = $patient->id;

        // Feedback opcional
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
