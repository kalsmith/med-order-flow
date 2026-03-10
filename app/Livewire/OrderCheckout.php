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
        'newName' => 'required|string|max:255',
        'newRut' => 'required|string', // Aquí podrías añadir tu regla de validación de RUT chileno
        'newRelationship' => 'required|in:child,spouse,parent,other', // IMPORTANTE: No permitir 'self' aquí
        'newBirthDate' => 'required|date',
        'newGender' => 'required|in:M,F',
    ]);

    // Limpiar el RUT (quitar puntos y guion) antes de guardar
    $cleanRut = preg_replace('/[^kK0-9]/', '', $this->newRut);

    $patient = Auth::user()->patients()->create([
        'full_name' => $this->newName,
        'rut' => $cleanRut,
        'relationship' => $this->newRelationship, // <--- AQUÍ ESTÁ EL CAMBIO: Usar la variable del form
        'birth_date' => $this->newBirthDate,
        'gender' => $this->newGender,
    ]);

    // Resetear el formulario y seleccionar al nuevo paciente
    $this->reset(['newName', 'newRut', 'newRelationship', 'newBirthDate', 'newGender', 'showNewPatientForm']);
    $this->selectPatient($patient->id);

    // Refrescar la lista de pacientes
    $this->patients = Auth::user()->patients()->get();
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
