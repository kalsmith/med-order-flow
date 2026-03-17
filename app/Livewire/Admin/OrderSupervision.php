<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Doctor;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

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

        // 1. Consulta base (Filtros que SÍ funcionan en SQL)
        $query = Order::with(['patient', 'doctor.user', 'activePrescription'])
            ->whereIn('status', ['paid', 'refund_pending', 'refunded', 'rejected'])
            ->when($this->doctorId, function($q) { $q->where('doctor_id', $this->doctorId); })
            ->when($this->dateFrom, function($q) { $q->whereDate('created_at', '>=', $this->dateFrom); })
            ->when($this->dateTo, function($q) { $q->whereDate('created_at', '<=', $this->dateTo); })
            ->latest();

        // 2. Traemos a memoria para desencriptar y filtrar
        $ordersCollection = $query->get();

        if ($this->searchPatient) {
            $term = mb_strtolower(trim($this->searchPatient), 'UTF-8');

            $ordersCollection = $ordersCollection->filter(function($order) use ($term) {
                if (!$order->patient) return false;

                // Laravel desencripta automáticamente aquí
                $nameMatch = str_contains(mb_strtolower($order->patient->full_name ?? '', 'UTF-8'), $term);
                $rutMatch  = str_contains(mb_strtolower($order->patient->rut ?? '', 'UTF-8'), $term);

                return $nameMatch || $rutMatch;
            });
        }

        // 3. Paginación Manual de la Colección
        $currentPage = Paginator::resolveCurrentPage(); // Esto reemplaza a $this->page
        $perPage = 15;
        $currentItems = $ordersCollection->forPage($currentPage, $perPage);

        $paginatedOrders = new LengthAwarePaginator(
            $currentItems,
            $ordersCollection->count(),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );

        return view('livewire.admin.order-supervision', [
            'orders' => $paginatedOrders,
            'doctors' => $doctors
        ]);
    }
}
