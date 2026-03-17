@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 fw-bold"><i class="bi bi-clipboard-check-fill text-primary"></i> Supervisión de Calidad Clínica</h2>
    </div>

    {{-- Cards de Resumen --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <h6 class="text-muted text-uppercase small fw-bold">Firmas Hoy</h6>
                    <h2 class="fw-bold mb-0 text-success">{{ $stats['signed_today'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <h6 class="text-muted text-uppercase small fw-bold">Órdenes Rechazadas</h6>
                    <h2 class="fw-bold mb-0 text-danger">{{ $stats['rejected_orders'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <h6 class="text-muted text-uppercase small fw-bold">Anulaciones Técnicas</h6>
                    <h2 class="fw-bold mb-0 text-warning">{{ $stats['voided_prescriptions'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Tabla de Médicos --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 fw-bold border-0">
                    <i class="bi bi-graph-up-arrow me-2 text-primary"></i>Rendimiento Médico (Auditoría DT)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light small text-muted text-uppercase">
                                <tr>
                                    <th>Médico</th>
                                    <th class="text-center">Asignadas</th>
                                    <th class="text-center">Firmadas</th>
                                    <th class="text-center">% Éxito</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($doctorPerformance as $doc)
                                <tr>
                                    <td class="fw-bold text-dark">
                                        {{-- El performance suele venir de un array o colección ya procesada --}}
                                        {{ $doc['name'] ?? 'Médico no identificado' }}
                                    </td>
                                    <td class="text-center">{{ $doc['total'] }}</td>
                                    <td class="text-center"><span class="badge bg-success rounded-pill px-3">{{ $doc['signed'] }}</span></td>
                                    <td class="text-center fw-bold">{{ $doc['efficiency'] }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Órdenes Recientes para Supervisar --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 fw-bold d-flex justify-content-between align-items-center border-0">
                    <span><i class="bi bi-list-ul me-2 text-primary"></i>Últimas Órdenes Generadas</span>
                    <span class="badge bg-light text-dark border fw-normal">Total: {{ count($latestOrders) }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($latestOrders as $order)
                        <div class="list-group-item list-group-item-action py-3 border-0 border-bottom">
                            <div class="d-flex w-100 justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-0 fw-bold {{ $order->patient ? 'text-dark' : 'text-danger italic' }}">
                                        {{ $order->patient->full_name ?? 'PACIENTE ELIMINADO' }}
                                    </h6>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>{{ $order->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <div class="d-flex flex-column align-items-end gap-1">
                                    <span class="badge {{ $order->status == 'paid' ? 'bg-primary-soft text-primary' : 'bg-light text-muted border' }}"
                                          style="font-size: 0.6rem; {{ $order->status == 'paid' ? 'background-color: #e7f1ff;' : '' }}">
                                        {{ strtoupper($order->status) }}
                                    </span>

                                    @if($order->activePrescription && $order->activePrescription->status === 'signed')
                                        <span class="badge bg-success-soft text-success border border-success" style="font-size: 0.6rem; background-color: #e8f5e9;">
                                            <i class="bi bi-pen-fill me-1"></i>FIRMADA
                                        </span>
                                    @else
                                        <span class="badge bg-warning-soft text-warning border border-warning" style="font-size: 0.6rem; background-color: #fff3cd;">
                                            <i class="bi bi-hourglass-split me-1"></i>PENDIENTE
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="small">
                                    <div class="text-dark mb-1">
                                        <span class="text-muted small text-uppercase fw-bold">Examen:</span> {{ Str::limit($order->display_name ?? 'No especificado', 30) }}
                                    </div>
                                    <div class="text-muted small">
                                        <i class="bi bi-person-badge me-1"></i>
                                        @if($order->doctor)
                                            {{ $order->doctor->user->name ?? 'Sin nombre' }}
                                        @else
                                            <span class="italic">Médico no asignado</span>
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary px-3 rounded-pill shadow-sm" style="font-size: 0.75rem;">
                                    Auditar <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <p class="text-muted italic small">No hay órdenes recientes para mostrar.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer bg-white py-3 text-center border-0">
                    <a href="{{ route('admin.orders.index') }}" class="text-primary fw-bold small text-decoration-none">
                        Ver panel general <i class="bi bi-chevron-right small"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
