<?php

namespace App\Livewire;

use App\Models\Doctor;
use App\Models\MedicalOrder;
use App\Models\Patient;
use App\Models\ExamType;
use App\Services\FlowService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomOrderForm extends Component
{
    public $exam;
    public $patients;
    public $selected_patient_id;
    public $showAddFamily = false;

    // --- PROPIEDADES PARA EL FORMULARIO DE NUEVO FAMILIAR ---
    public $new_full_name;
    public $new_rut;
    public $new_relationship;
    public $new_birth_date;
    public $new_gender_biologic = 'M';

    // --- PROPIEDADES DE LA ORDEN ---
    public $custom_exam_name;
    public $symptoms;
    public $patient_type = 'adulto';
    public $urgency = 'normal';

    public function mount($examId = null)
    {
        if ($examId) {
            $this->exam = ExamType::find($examId);
        }

        $this->loadPatients();

        // Seleccionar por defecto al titular (self)
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
        if ($this->showAddFamily) {
            $this->selected_patient_id = null;
        } else {
            // Si cancela, re-seleccionar al titular
            $primary = $this->patients->where('relationship', 'self')->first();
            $this->selected_patient_id = $primary ? $primary->id : null;
        }
    }

    public function saveFamily()
    {
        // 1. Validación estricta
        $this->validate([
            'new_full_name'       => 'required|string|min:3',
            'new_rut'             => 'required|string|min:7',
            'new_relationship'    => 'required|in:hijo,conyuge,padre,otro',
            'new_birth_date'      => 'required|date',
            'new_gender_biologic' => 'required|in:M,F',
        ]);

        // 2. Limpieza de RUT
        $cleanRut = preg_replace('/[^kK0-9]/', '', $this->new_rut);

        // 3. Creación asegurando que NO sea 'self'
        $patient = Auth::user()->patients()->create([
            'full_name'       => $this->new_full_name,
            'rut'             => $cleanRut,
            'relationship'    => $this->new_relationship, // hijo, conyuge, etc.
            'birth_date'      => $this->new_birth_date,
            'gender_biologic' => $this->new_gender_biologic,
            'is_primary'      => 0, // Importante: Familiar nunca es principal
            'prevision'       => 'Particular',
        ]);

        // 4. Reset de campos
        $this->reset([
            'new_full_name',
            'new_rut',
            'new_relationship',
            'new_birth_date',
            'new_gender_biologic',
            'showAddFamily'
        ]);

        // 5. Refrescar lista y seleccionar al nuevo
        $this->loadPatients();
        $this->selected_patient_id = $patient->id;

        session()->flash('success', 'Familiar registrado correctamente.');
    }

    public function submit()
    {
        $this->validate([
            'custom_exam_name'    => 'required|min:5',
            'selected_patient_id' => 'required',
        ]);

        $doctor = Doctor::where('is_active', true)->first();

        if (!$doctor) {
            session()->flash('error', 'No hay médicos disponibles.');
            return;
        }

        try {
            $order = DB::transaction(function () use ($doctor) {
                return MedicalOrder::create([
                    'id'                 => (string) Str::uuid(),
                    'patient_id'         => $this->selected_patient_id,
                    'doctor_id'          => $doctor->id,
                    'exam_type_id'       => null,
                    'custom_description' => $this->custom_exam_name,
                    'status'             => 'pending',
                    'type'               => 'custom',
                    'amount'             => 9990,
                    'verification_code'  => strtoupper(Str::random(8)),
                ]);
            });

            $flowService = app(FlowService::class);
            $flowResponse = $flowService->createPayment($order);

            if ($flowResponse && isset($flowResponse->token)) {
                return redirect()->away($flowResponse->url . "?token=" . $flowResponse->token);
            }

            throw new \Exception('Error al generar el pago en Flow.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error en CustomOrderForm: " . $e->getMessage());
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.custom-order-form');
    }
}
