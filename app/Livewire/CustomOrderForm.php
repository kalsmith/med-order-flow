<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CustomOrderForm extends Component
{
    // Propiedades del formulario
    public $selected_patient_id;
    public $custom_exam_name;
    public $symptoms;
    public $patient_type = 'adulto';
    public $urgency = 'normal';

    public function mount()
    {
        // Cargamos el ID del perfil "self" por defecto
        $self = Auth::user()->patients()->where('relationship', 'self')->first();
        $this->selected_patient_id = $self ? $self->id : null;
    }

    public function selectPatient($id)
    {
        $this->selected_patient_id = $id;
    }

    public function submit()
    {
        $this->validate([
            'selected_patient_id' => 'required|exists:patients,id',
            'custom_exam_name' => 'required|min:3',
            'symptoms' => 'nullable|string',
            'patient_type' => 'required',
            'urgency' => 'required',
        ]);

        // Guardamos en sesión para el checkout (o procesamos directo)
        session([
            'order_type' => 'custom',
            'order_data' => [
                'patient_id' => $this->selected_patient_id,
                'exams' => $this->custom_exam_name,
                'symptoms' => $this->symptoms,
                'type' => $this->patient_type,
                'urgency' => $this->urgency,
                'price' => 9990
            ]
        ]);

        return redirect()->route('checkout.index');
    }

    public function render()
    {
        return view('livewire.custom-order-form', [
            'patients' => Auth::user()->patients
        ]);
    }
}
