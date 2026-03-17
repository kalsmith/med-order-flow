<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Doctor;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    // 1. Traemos las órdenes con sus relaciones (OJO: Sin paginar aún)
    $query = Order::with(['patient', 'doctor.user', 'activePrescription'])
        ->whereIn('status', ['paid', 'refund_pending', 'refunded', 'rejected'])
        ->when($this->doctorId, function($q) { $q->where('doctor_id', $this->doctorId); })
        ->when($this->dateFrom, function($q) { $q->whereDate('created_at', '>=', $this->dateFrom); })
        ->when($this->dateTo, function($q) { $q->whereDate('created_at', '<=', $this->dateTo); })
        ->latest();

    // 2. Obtenemos los resultados y filtramos en PHP
    $orders = $query->get();

    if ($this->searchPatient) {
        $term = mb_strtolower(trim($this->searchPatient), 'UTF-8');

        $orders = $orders->filter(function($order) use ($term) {
            if (!$order->patient) return false;

            // Al acceder a full_name o rut, Laravel los desencripta en tiempo real
            $nameMatch = str_contains(mb_strtolower($order->patient->full_name, 'UTF-8'), $term);
            $rutMatch  = str_contains(mb_strtolower($order->patient->rut, 'UTF-8'), $term);

            return $nameMatch || $rutMatch;
        });
    }

    // 3. Paginar manualmente (Livewire necesita un LengthAwarePaginator si lo haces así)
    // Para simplificar ahora y probar que funciona, usemos una paginación simple de colección:
    $paginatedOrders = new \Illuminate\Pagination\LengthAwarePaginator(
        $orders->forPage($this->page, 15),
        $orders->count(),
        15,
        $this->page,
        ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
    );

    return view('livewire.admin.order-supervision', [
        'orders' => $paginatedOrders,
        'doctors' => $doctors
    ]);
}


}
