<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;

class CustomOrderForm extends Component
{
    // Campos de la Orden
    public $selected_patient_id;
    public $custom_exam_name;
    public $symptoms;
    public $patient_type = 'adulto';
    public $urgency = 'normal';

    // Estado del Formulario de Familiar
    public $showAddFamily = false;

    // Campos para Nuevo Familiar
    public $new_full_name;
    public $new_rut;
    public $new_relationship = '';

    public function mount()
    {
        // Seleccionamos al usuario principal por defecto si existe
        $self = Auth::user()->patients()->where('relationship', 'self')->first();
        if ($self) {
            $this->selected_patient_id = $self->id;
        }
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
    // 1. Limpiamos el RUT antes de validar (quitamos puntos y guion)
    // Usamos el mismo patrón que en tus otros componentes para mantener la consistencia
    $this->new_rut = preg_replace('/[^kK0-9]/', '', $this->new_rut);

    // 2. Ahora validamos el RUT limpio
    $this->validate([
        'new_full_name' => 'required|min:3|string',
        'new_rut' => 'required|string|min:7|max:9', // Ahora sí pasará porque mide max 9
        'new_relationship' => 'required|string',
        'new_birth_date' => 'required|date', // <--- NUEVO
        'new_gender' => 'required|in:M,F'     // <--- NUEVO
    ], [
        'new_full_name.required' => 'El nombre es obligatorio',
        'new_relationship.required' => 'Indica el parentesco',
        'new_rut.max' => 'El RUT no es válido (demasiados caracteres)',
        'new_rut.min' => 'El RUT es muy corto'
    ]);

    // 3. Guardamos (ya viene limpio)
    $patient = Auth::user()->patients()->create([
        'full_name' => $this->new_full_name,
        'rut' => $this->new_rut,
        'relationship' => $this->new_relationship,
        'birth_date' => $this->new_birth_date,     // <--- NUEVO
        'gender_biologic' => $this->new_gender,    // <--- NUEVO (usa el nombre exacto de tu DB)
        'is_active' => true,
        // Agregamos valores por defecto consistentes con tu DB
        'prevision' => 'Particular'
    ]);

    // Seleccionamos automáticamente al nuevo paciente
    $this->selected_patient_id = $patient->id;
    $this->showAddFamily = false;

    // Reset de campos
    $this->reset(['new_full_name', 'new_rut', 'new_relationship']);

    session()->flash('patient_added', 'Familiar agregado correctamente.');
}

    public function submit()
    {
        $this->validate([
            'selected_patient_id' => 'required',
            'custom_exam_name' => 'required|min:5',
        ]);

        // Aquí rediriges a la lógica de guardado/pago
        return redirect()->route('orders.custom.confirm', [
            'patient_id' => $this->selected_patient_id,
            'exams' => $this->custom_exam_name,
            'symptoms' => $this->symptoms,
            'type' => $this->patient_type,
            'urgency' => $this->urgency
        ]);
    }

    public function render()
    {
        return view('livewire.custom-order-form', [
            'patients' => Auth::user()->patients()->orderBy('relationship', 'asc')->get()
        ]);
    }
}
