@extends('layouts.app')

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
                <div class="card-header bg-white fw-bold">Últimas Órdenes Generadas</div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($latestOrders as $order)
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $order->patient->full_name }}</h6>
                                <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1 text-sm text-muted">
                                Examen: {{ $order->display_name }} <br>
                                Médico: {{ $order->doctor ? $order->doctor->name : 'Pendiente' }}
                            </p>
                            <span class="badge {{ $order->status == 'paid' ? 'bg-primary' : 'bg-secondary' }}">
                                {{ strtoupper($order->status) }}
                            </span>
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-link p-0 float-end text-decoration-none">Ver Ficha</a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
