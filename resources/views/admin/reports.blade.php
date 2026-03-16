@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Reportes de Gestión</h2>
    </div>

    <div class="row g-4">
        {{-- Top Exámenes --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                    <i class="bi bi-flask text-primary me-2"></i>
                    <h5 class="mb-0 fw-bold">Top 5 Exámenes Más Solicitados</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @forelse($popularExams as $item)
                        <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                            <div>
                                <span class="fw-medium">{{ $item->examType->name ?? 'Examen Manual/Personalizado' }}</span>
                            </div>
                            <span class="badge bg-primary rounded-pill">{{ $item->total }}</span>
                        </div>
                        @empty
                        <p class="text-muted">Sin datos suficientes.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Estados de Órdenes --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                    <i class="bi bi-activity text-success me-2"></i>
                    <h5 class="mb-0 fw-bold">Distribución por Estado</h5>
                </div>
                <div class="card-body">
                    @forelse($orderStats as $stat)
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-uppercase small fw-bold text-muted">{{ $stat->status }}</span>
                            <span class="badge bg-light text-dark">{{ $stat->total }}</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            @php
                                $color = match($stat->status) {
                                    'paid' => 'bg-success',
                                    'pending' => 'bg-warning',
                                    'canceled' => 'bg-danger',
                                    default => 'bg-info'
                                };
                            @endphp
                            <div class="progress-bar {{ $color }}" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-4">No hay órdenes registradas.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
