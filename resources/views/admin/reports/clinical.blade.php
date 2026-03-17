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
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
            <span>Últimas Órdenes Generadas</span>
            <span class="badge bg-light text-dark border fw-normal">Total: {{ count($latestOrders) }}</span>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @foreach($latestOrders as $order)
                <div class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-0 fw-bold">{{ $order->patient->full_name }}</h6>
                            <small class="text-muted" style="font-size: 0.75rem;">
                                <i class="bi bi-clock"></i> {{ $order->created_at->diffForHumans() }}
                            </small>
                        </div>
                        <div class="text-end">
                            {{-- Estado de Pago --}}
                            <span class="badge {{ $order->status == 'paid' ? 'bg-primary' : 'bg-secondary' }} mb-1 d-block" style="font-size: 0.65rem;">
                                {{ strtoupper($order->status) }}
                            </span>

                            {{-- ESTADO DE FIRMA MÉDICA --}}
                            @if($order->activePrescription && $order->activePrescription->status === 'signed')
                                <span class="badge bg-success" style="font-size: 0.65rem;">
                                    <i class="bi bi-pen-fill"></i> FIRMADA
                                </span>
                            @else
                                <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">
                                    <i class="bi bi-hourglass-split"></i> PENDIENTE FIRMA
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-end">
                        <p class="mb-0 text-muted small">
                            <span class="d-block text-dark">
                                <strong>Examen:</strong> {{ Str::limit($order->display_name, 40) }}
                            </span>
                            <span class="d-block">
                                <strong>Médico:</strong> {{ $order->doctor ? $order->doctor->user->name : 'No asignado' }}
                            </span>
                        </p>
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">
                            Ver Auditoría <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer bg-light text-center">
            <a href="{{ route('admin.orders.index') }}" class="small text-decoration-none">Ver todas las órdenes</a>
        </div>
    </div>
</div>
    </div>
</div>
@endsection
