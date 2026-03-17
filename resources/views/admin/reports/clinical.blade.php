@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 fw-bold mb-0"><i class="bi bi-clipboard-check-fill text-primary me-2"></i>Supervisión de Calidad Clínica</h2>
        <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i> Auditoría en tiempo real</span>
    </div>

    {{-- Cards de Resumen --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="rounded-circle bg-success-soft d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px; background-color: #e8f5e9;">
                        <i class="bi bi-patch-check text-success fs-4"></i>
                    </div>
                    <h6 class="text-muted text-uppercase small fw-bold">Firmas Hoy</h6>
                    <h2 class="fw-bold mb-0 text-dark">{{ $stats['signed_today'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="rounded-circle bg-danger-soft d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px; background-color: #ffebee;">
                        <i class="bi bi-x-circle text-danger fs-4"></i>
                    </div>
                    <h6 class="text-muted text-uppercase small fw-bold">Órdenes Rechazadas</h6>
                    <h2 class="fw-bold mb-0 text-dark">{{ $stats['rejected_orders'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="rounded-circle bg-warning-soft d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px; background-color: #fff8e1;">
                        <i class="bi bi-slash-circle text-warning fs-4"></i>
                    </div>
                    <h6 class="text-muted text-uppercase small fw-bold">Anulaciones Técnicas</h6>
                    <h2 class="fw-bold mb-0 text-dark">{{ $stats['voided_prescriptions'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Tabla de Médicos --}}
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 fw-bold border-0">
                    <i class="bi bi-graph-up-arrow me-2 text-primary"></i>Rendimiento Médico (Auditoría DT)
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="small text-muted text-uppercase">
                                <tr>
                                    <th class="border-0 ps-0">Médico</th>
                                    <th class="text-center border-0">Asignadas</th>
                                    <th class="text-center border-0">Firmadas</th>
                                    <th class="text-center border-0">% Éxito</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($doctorPerformance as $doc)
                                <tr>
                                    <td class="fw-bold text-dark ps-0">
                                        {{ $doc['name'] }}
                                    </td>
                                    <td class="text-center">{{ $doc['total'] }}</td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill px-3" style="background-color: #e8f5e9; color: #2e7d32;">
                                            {{ $doc['signed'] }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold text-primary">
                                        {{ $doc['efficiency'] }}%
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No hay actividad médica registrada.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Órdenes Recientes para Supervisar --}}
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 fw-bold d-flex justify-content-between align-items-center border-0">
                    <span><i class="bi bi-list-ul me-2 text-primary"></i>Últimas Órdenes Generadas</span>
                    <span class="badge bg-light text-dark border fw-normal">Mostrando {{ $latestOrders->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($latestOrders as $order)
                        <div class="list-group-item list-group-item-action py-3 border-0 border-bottom">
                            <div class="d-flex w-100 justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-0 fw-bold">
                                        {{ $order->patient->full_name ?? 'Paciente no identificado' }}
                                    </h6>
                                    <small class="text-muted">
                                        <i class="bi bi-fingerprint me-1 small"></i> ID: {{ substr($order->id, 0, 8) }} |
                                        <i class="bi bi-clock ms-2 me-1"></i>{{ $order->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    {{-- Estado de Pago --}}
                                    <span class="badge mb-1 d-block"
                                          style="font-size: 0.65rem; background-color: {{ $order->status == 'paid' ? '#e7f1ff' : '#f8f9fa' }}; color: {{ $order->status == 'paid' ? '#0d6efd' : '#6c757d' }}; border: 1px solid {{ $order->status == 'paid' ? '#cfe2ff' : '#dee2e6' }};">
                                        {{ strtoupper($order->status) }}
                                    </span>

                                    {{-- Estado de Firma (Basado en Prescription vinculada) --}}
                                    @php $signed = $order->activePrescription && $order->activePrescription->status === 'signed'; @endphp
                                    <span class="badge"
                                          style="font-size: 0.65rem; background-color: {{ $signed ? '#e8f5e9' : '#fff3cd' }}; color: {{ $signed ? '#198754' : '#856404' }}; border: 1px solid {{ $signed ? '#c3e6cb' : '#ffeeba' }};">
                                        <i class="bi {{ $signed ? 'bi-check2-circle' : 'bi-hourglass' }} me-1"></i>
                                        {{ $signed ? 'FIRMADA' : 'PENDIENTE FIRMA' }}
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-end mt-2">
                                <div class="small">
                                    <div class="mb-1">
                                        <span class="text-muted fw-bold small text-uppercase" style="font-size: 0.6rem;">Examen:</span>
                                        <span class="text-dark fw-medium">{{ $order->examType->name ?? $order->custom_description ?? 'No especificado' }}</span>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        <i class="bi bi-person-badge-fill me-1"></i>
                                        @if($order->doctor)
                                            {{ $order->doctor->prefix }} {{ $order->doctor->user->name }}
                                        @elseif($order->activePrescription && $order->activePrescription->doctor)
                                            {{ $order->activePrescription->doctor->user->name }}
                                        @else
                                            <span class="text-warning small italic">Esperando toma de médico</span>
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-light border px-3 rounded-pill shadow-xs" style="font-size: 0.75rem;">
                                    Auditar <i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <i class="bi bi- inbox fs-1 text-light mb-2 d-block"></i>
                            <p class="text-muted italic small">No hay órdenes para mostrar hoy.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer bg-light py-3 text-center border-0">
                    <a href="{{ route('admin.orders.index') }}" class="text-primary fw-bold small text-decoration-none">
                        Ver panel general de gestión <i class="bi bi-arrow-right small"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
