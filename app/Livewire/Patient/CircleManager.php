<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use App\Support\RutHelper;

class CircleManager extends Component
{
    public $showAddModal = false;
    public $showDeleteModal = false; // Nuevo modal elegante
    public $memberIdToDelete = null; // ID en espera para borrar

    // Campos del formulario
    public $full_name, $rut, $relationship, $birth_date, $gender_biologic = 'Masculino';

    protected $rules = [
        'full_name'       => 'required|string|min:8',
        'rut'             => 'required|string|min:7',
        'relationship'    => 'required|in:hijo,conyuge,padre,otro',
        'birth_date'      => 'required|date|before:today',
        'gender_biologic' => 'required|in:Masculino,Femenino',
    ];

    public function openModal()
    {
        $this->resetInput();
        $this->showAddModal = true;
    }

    public function closeModal()
    {
        $this->showAddModal = false;
    }

    private function resetInput()
    {
        $this->reset(['full_name', 'rut', 'relationship', 'birth_date', 'gender_biologic', 'memberIdToDelete']);
    }

    /**
     * Lógica para agregar familiar
     */
    public function save()
    {
        $this->validate();

        if (!RutHelper::validate($this->rut)) {
            $this->addError('rut', 'RUT no válido.');
            return;
        }

        Auth::user()->patients()->create([
            'full_name'       => $this->full_name,
            'rut'             => $this->rut, // El setter en el modelo lo limpiará
            'relationship'    => $this->relationship,
            'birth_date'      => $this->birth_date,
            'gender_biologic' => $this->gender_biologic,
            'is_primary'      => false,
            'prevision'       => 'Particular',
        ]);

        $this->closeModal();
        session()->flash('success', 'Familiar añadido correctamente.');
    }

    /**
     * Paso 1: Abrir modal de confirmación
     */
    public function confirmDeletion($id)
    {
        $this->memberIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Paso 2: Ejecutar el borrado real (Sin alerts de navegador)
     */
    public function deleteMember()
    {
        if (!$this->memberIdToDelete) return;

        $member = Auth::user()->patients()->findOrFail($this->memberIdToDelete);

        if (!$member->is_primary) {
            $member->delete();
            session()->flash('success', 'Familiar eliminado de tu círculo.');
        }

        $this->showDeleteModal = false;
        $this->memberIdToDelete = null;
    }

    public function render()
    {
        return view('livewire.patient.circle-manager', [
            'members' => Auth::user()->patients()
                            ->orderBy('is_primary', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->get()
        ]);
    }
}
