<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Doctor;
use Livewire\Component;
use Livewire\WithPagination;

class OrderSupervision extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Propiedades de filtrado
    public $searchPatient = '';
    public $doctorId = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $status = '';

    // Resetear página cuando cambian los filtros
    public function updatingSearchPatient() { $this->resetPage(); }
    public function updatingDoctorId() { $this->resetPage(); }

    public function render()
    {
        $doctors = Doctor::with('user')->get();

        $orders = Order::with(['patient', 'doctor.user', 'activePrescription'])
            ->when($this->searchPatient, function($query) {
                $query->whereHas('patient', function($q) {
                    $q->where('full_name', 'like', '%' . $this->searchPatient . '%')
                      ->orWhere('rut', 'like', '%' . $this->searchPatient . '%');
                });
            })
            ->when($this->doctorId, function($query) {
                $query->where('doctor_id', $this->doctorId);
            })
            ->when($this->dateFrom, function($query) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function($query) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.order-supervision', [
            'orders' => $orders,
            'doctors' => $doctors
        ]);
    }
}
