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
    public $selectedPatientId;
    public $showNewPatientForm = false;

    // Campos para registro de familiar
    public $newName;
    public $newRut;
    public $newRelationship;
    public $newBirthDate;
    public $newGender = 'masculino';

    public function mount($examId)
    {
        $this->exam = ExamType::findOrFail($examId);
        $this->loadPatients();

        // Seleccionamos al titular ("self") por defecto si ya existe
        $primary = $this->patients->where('relationship', 'self')->first();
        if ($primary) {
            $this->selectedPatientId = $primary->id;
        }
    }

    public function loadPatients()
    {
        // Traemos todos los pacientes asociados al usuario de Google
        $this->patients = Auth::user()->patients;
    }

    public function selectPatient($id)
    {
        $this->selectedPatientId = $id;
        $this->showNewPatientForm = false;
    }

    public function toggleNewPatient()
    {
        $this->showNewPatientForm = !$this->showNewPatientForm;
        if ($this->showNewPatientForm) {
            $this->selectedPatientId = null;
        }
    }

    public function saveNewPatient()
    {
        $this->validate([
            'newName' => 'required|string|min:5',
            'newRut' => 'required|string', // Aquí puedes añadir validación cl_rut
            'newRelationship' => 'required|in:child,spouse,parent,other',
            'newBirthDate' => 'required|date|before:today',
        ], [
            'newName.required' => 'El nombre es obligatorio.',
            'newRelationship.required' => 'Indica el parentesco.',
        ]);

        $patient = Auth::user()->patients()->create([
            'full_name' => $this->newName,
            'rut' => $this->newRut,
            'relationship' => $this->newRelationship,
            'birth_date' => $this->newBirthDate,
            'gender_biologic' => $this->newGender,
            'is_primary' => false
        ]);

        $this->loadPatients();
        $this->selectedPatientId = $patient->id;
        $this->showNewPatientForm = false;

        // Limpiar campos
        $this->reset(['newName', 'newRut', 'newRelationship', 'newBirthDate']);
    }

    public function render()
    {
        $selectedPatient = $this->selectedPatientId
            ? Patient::find($this->selectedPatientId)
            : null;

        return view('livewire.order-checkout', [
            'selectedPatient' => $selectedPatient
        ]);
    }
}
