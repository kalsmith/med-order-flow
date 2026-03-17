@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-clipboard-check-fill text-primary"></i> Supervisión de Calidad Clínica</h2>
    </div>

    {{-- Cards de Resumen --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-light border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Firmas Hoy</h6>
                    <h2 class="fw-bold">{{ $stats['signed_today'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Órdenes Rechazadas</h6>
                    <h2 class="fw-bold text-danger">{{ $stats['rejected_orders'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Anulaciones Técnicas</h6>
                    <h2 class="fw-bold text-warning">{{ $stats['voided_prescriptions'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Tabla de Médicos --}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Rendimiento Médico (Auditoría DT)</div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Médico</th>
                                <th>Asignadas</th>
                                <th>Firmadas</th>
                                <th>% Éxito</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($doctorPerformance as $doc)
                            <tr>
                                <td>{{ $doc['name'] }}</td>
                                <td>{{ $doc['total'] }}</td>
                                <td><span class="badge bg-success">{{ $doc['signed'] }}</span></td>
                                <td>{{ $doc['efficiency'] }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>





{{-- Órdenes Recientes para Supervisar --}}
<div class="col-md-6">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 fw-bold d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul me-2"></i>Últimas Órdenes Generadas</span>
            <span class="badge bg-light text-dark border fw-normal">Total: {{ count($latestOrders) }}</span>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @foreach($latestOrders as $order)
                <div class="list-group-item list-group-item-action py-3">
                    {{-- Fila Superior: Paciente y Estados --}}
                    <div class="d-flex w-100 justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="mb-1 fw-bold text-dark">{{ $order->patient->full_name }}</h6>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>{{ $order->created_at->diffForHumans() }}
                            </small>
                        </div>
                        <div class="d-flex flex-column align-items-end gap-1">
                            {{-- Estado de Pago --}}
                            <span class="badge {{ $order->status == 'paid' ? 'bg-primary-soft text-primary border border-primary' : 'bg-secondary' }}"
                                  style="font-size: 0.65rem; {{ $order->status == 'paid' ? 'background-color: #e7f1ff;' : '' }}">
                                {{ strtoupper($order->status) }}
                            </span>

                            {{-- Estado de Firma --}}
                            @if($order->activePrescription && $order->activePrescription->status === 'signed')
                                <span class="badge bg-success-soft text-success border border-success" style="font-size: 0.65rem; background-color: #e8f5e9;">
                                    <i class="bi bi-pen-fill me-1"></i>FIRMADA
                                </span>
                            @else
                                <span class="badge bg-warning-soft text-warning border border-warning" style="font-size: 0.65rem; background-color: #fff3cd;">
                                    <i class="bi bi-hourglass-split me-1"></i>PENDIENTE
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Fila Inferior: Detalles y Botón --}}
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="small">
                            <div class="text-dark mb-1">
                                <span class="text-muted">Examen:</span> {{ Str::limit($order->display_name, 35) }}
                            </div>
                            <div class="text-muted">
                                <i class="bi bi-person-badge me-1"></i>{{ $order->doctor ? $order->doctor->user->name : 'Médico no asignado' }}
                            </div>
                        </div>
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary px-3 rounded-pill" style="font-size: 0.75rem;">
                            Ver Auditoría <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer bg-white py-3 text-center">
            <a href="{{ route('admin.orders.index') }}" class="text-primary fw-bold small text-decoration-none">
                Ver todas las órdenes <i class="bi bi-chevron-right small"></i>
            </a>
        </div>
    </div>
</div>







    </div>
</div>
@endsection
