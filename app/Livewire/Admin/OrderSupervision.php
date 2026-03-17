<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Doctor;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class OrderSupervision extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $searchPatient = '';
    public $doctorId = '';
    public $dateFrom = '';
    public $dateTo = '';

    public function updatingSearchPatient() { $this->resetPage(); }
    public function updatingDoctorId() { $this->resetPage(); }
    public function updatingDateFrom() { $this->resetPage(); }
    public function updatingDateTo() { $this->resetPage(); }

    public function clearFilters()
    {
        $this->reset(['searchPatient', 'doctorId', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function render()
    {
        $doctors = Doctor::with('user')->get();

        $orders = Order::with(['patient', 'doctor.user', 'activePrescription'])
            ->whereIn('status', ['paid', 'refund_pending', 'refunded', 'rejected'])
            ->when($this->searchPatient, function($query) {
                // Convertimos la búsqueda a minúsculas desde PHP
                $term = '%' . mb_strtolower($this->searchPatient, 'UTF-8') . '%';

                $query->whereHas('patient', function($q) use ($term) {
                    $q->where(function($sub) use ($term) {
                        // Forzamos LOWER tanto en la columna como en el parámetro para máxima compatibilidad
                        $sub->whereRaw('LOWER(full_name) LIKE ?', [$term])
                            ->orWhereRaw('LOWER(rut) LIKE ?', [$term]);
                    });
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
