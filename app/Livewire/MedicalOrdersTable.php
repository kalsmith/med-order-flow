<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MedicalOrder;
use Illuminate\Support\Facades\Auth;

class MedicalOrdersTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        // Misma lógica de filtrado que tenías en el controlador
        $query = MedicalOrder::with(['patient', 'examType', 'doctor.user'])
            ->latest();

        if (Auth::user()->hasRole('doctor')) {
            // Doctores ven lo disponible para firmar o lo que ya firmaron
            $query->whereIn('status', ['paid', 'signed', 'pending']);
        }

        return view('livewire.medical-orders-table', [
            'orders' => $query->paginate(10)
        ]);
    }
}
