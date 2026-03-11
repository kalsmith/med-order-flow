<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use App\Support\RutHelper;

class CustomOrderFlow extends Component
{
    public $patients;
    public $selected_patient_id;
    public $showAddFamily = false;

    // Campos para Orden Custom
    public $description;

    // Campos para Nuevo Familiar
    public $new_full_name, $new_rut, $new_relationship, $new_birth_date, $new_gender_biologic = 'Masculino';

    public function mount()
    {
        $this->loadPatients();
        $primary = $this->patients->where('relationship', 'self')->first();
        if ($primary) $this->selected_patient_id = $primary->id;
    }

    public function loadPatients()
    {
        $this->patients = Auth::user()->patients()->get();
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
            'new_full_name' => 'required|string|min:8',
            'new_rut' => 'required|string',
            'new_relationship' => 'required',
            'new_birth_date' => 'required|date|before:today',
            'new_gender_biologic' => 'required',
        ]);

        if (!RutHelper::validate($this->new_rut)) {
            $this->addError('new_rut', 'RUT no válido.');
            return;
        }

        $patient = Auth::user()->patients()->create([
            'full_name' => $this->new_full_name,
            'rut' => $this->new_rut,
            'relationship' => $this->new_relationship,
            'birth_date' => $this->new_birth_date,
            'gender_biologic' => $this->new_gender_biologic,
            'is_primary' => false,
        ]);

        $this->reset(['new_full_name', 'new_rut', 'new_relationship', 'new_birth_date', 'showAddFamily']);
        $this->loadPatients();
        $this->selected_patient_id = $patient->id;
    }

    public function submitRequest()
    {
        $this->validate([
            'selected_patient_id' => 'required',
            'description' => 'required|min:10',
        ]);

        // Aquí guardaremos la orden custom más adelante
        session()->flash('success', 'Solicitud enviada. Un médico te contactará.');
        return redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.custom-order-flow');
    }
}
