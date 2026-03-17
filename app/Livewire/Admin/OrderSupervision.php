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

        $query = Order::with(['patient', 'doctor.user', 'activePrescription'])
            ->whereIn('status', ['paid', 'refund_pending', 'refunded', 'rejected'])
            ->when($this->searchPatient, function($q_parent) {
                // 1. Limpiamos espacios al inicio/final del input (TRIM de PHP)
                $cleanInput = trim($this->searchPatient);
                $term = '%' . mb_strtolower($cleanInput, 'UTF-8') . '%';

                Log::info('--- INICIO BÚSQUEDA ---');
                Log::info('Input original: "' . $this->searchPatient . '"');
                Log::info('Término procesado: "' . $term . '"');

                $q_parent->whereHas('patient', function($q) use ($term) {
                    $q->where(function($sub) use ($term) {
                        // 2. Aplicamos TRIM() en la base de datos para ignorar espacios fantasmas
                        $sub->whereRaw('LOWER(TRIM(full_name)) LIKE ?', [$term])
                            ->orWhereRaw('LOWER(TRIM(rut)) LIKE ?', [$term]);
                    });
                });
            })
            ->when($this->doctorId, function($q) {
                $q->where('doctor_id', $this->doctorId);
            })
            ->when($this->dateFrom, function($q) {
                $q->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function($q) {
                $q->whereDate('created_at', '<=', $this->dateTo);
            })
            ->latest();

        // 3. LOG DEL SQL GENERADO (Descomenta si necesitas ver la query exacta)
        // Log::debug('SQL: ' . $query->toSql());
        // Log::debug('Bindings: ', $query->getBindings());

        $orders = $query->paginate(15);

        if($this->searchPatient) {
            Log::info('Resultados encontrados: ' . $orders->total());
            Log::info('--- FIN BÚSQUEDA ---');
        }

        return view('livewire.admin.order-supervision', [
            'orders' => $orders,
            'doctors' => $doctors
        ]);
    }
}
