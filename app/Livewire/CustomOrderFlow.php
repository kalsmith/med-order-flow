<?php

namespace App\Livewire;

use App\Models\Doctor;
use App\Models\MedicalOrder;
use Livewire\Component;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Support\RutHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomOrderFlow extends Component
{
    public $patients;
    public $selected_patient_id;
    public $showAddFamily = false;

    // Campos para la Orden Custom
    public $description;

    // Campos para Nuevo Familiar (Sincronizados con OrderCheckout)
    public $new_full_name;
    public $new_rut;
    public $new_relationship;
    public $new_birth_date;
    public $new_gender_biologic = 'Masculino'; // Valor inicial

    public function mount()
    {
        $this->loadPatients();
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

        if (!RutHelper::validate($this->new_rut)) {
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

            $this->reset(['new_full_name', 'new_rut', 'new_relationship', 'new_birth_date', 'showAddFamily']);
            $this->loadPatients();
            $this->selected_patient_id = $patient->id;

            session()->flash('message', 'Familiar añadido correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al guardar familiar en CustomFlow: " . $e->getMessage());
            $this->addError('new_rut', 'No se pudo guardar: ' . $e->getMessage());
        }
    }


    public function submitRequest()
{
    Log::info("=== INICIO PROCESO DE PAGO ===");
    Log::info("1. Validando datos del paciente ID: " . $this->selected_patient_id);

    $this->validate([
        'selected_patient_id' => 'required',
        'description' => 'required|min:10',
    ]);

    $doctor = Doctor::where('is_active', true)->first();
    if (!$doctor) {
        Log::info("X. Fallo: No hay doctores activos.");
        $this->addError('description', 'Lo sentimos, no hay médicos disponibles.');
        return;
    }

    try {
        $order = DB::transaction(function () use ($doctor) {
            return MedicalOrder::create([
                'id'                 => (string) Str::uuid(),
                'patient_id'         => $this->selected_patient_id,
                'doctor_id'          => $doctor->id,
                'exam_type_id'       => null,
                'custom_description' => $this->description,
                'status'             => 'pending',
                'type'               => 'custom',
                'amount'             => 9990,
                'verification_code'  => strtoupper(Str::random(8)),
            ]);
        });

        Log::info("2. Orden guardada en DB: " . $order->id);
        Log::info("3. Disparando evento 'trigger-payment-submit' hacia el navegador...");

        // Usamos la sintaxis completa de Livewire 3
        $this->dispatch('trigger-payment-submit');

        Log::info("4. Evento disparado. Fin de lógica PHP.");

    } catch (\Exception $e) {
        Log::info("ERROR CRÍTICO: " . $e->getMessage());
        $this->addError('description', 'No pudimos procesar la solicitud.');
    }
}


    public function render()
    {
        return view('livewire.custom-order-flow');
    }
}
