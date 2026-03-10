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

        // Cargamos los pacientes asegurando que sea una colección
        $this->loadPatients();

        // Ahora el where() no fallará porque $this->patients es una colección (aunque esté vacía)
        $primary = $this->patients->where('relationship', 'self')->first();

        if ($primary) {
            $this->selectedPatientId = $primary->id;
        }
    }

    public function loadPatients()
    {
        // Usamos la relación y obtenemos el resultado explícitamente como colección
        $this->patients = Auth::user()->patients()->get() ?? collect();
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
            'newRut' => 'required|string',
            'newRelationship' => 'required|in:child,spouse,parent,other',
            'newBirthDate' => 'required|date|before:today',
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

        $this->reset(['newName', 'newRut', 'newRelationship', 'newBirthDate']);
    }

    public function render()
    {
        // Evitamos buscar si no hay ID seleccionado
        $selectedPatient = $this->selectedPatientId
            ? Patient::find($this->selectedPatientId)
            : null;

        return view('livewire.order-checkout', [
            'selectedPatient' => $selectedPatient
        ]);
    }
}
