@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Contabilidad y Liquidaciones</h2>
            <p class="text-muted small">Resumen financiero general y balance de honorarios médicos.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-white text-dark shadow-sm border px-3 py-2 rounded-pill">
                <i class="bi bi-calendar3 me-2"></i>Corte al: {{ now()->format('d/m/Y') }}
            </span>
        </div>
    </div>

    {{-- Widgets Financieros --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <h6 class="text-uppercase opacity-75 small fw-bold">Recaudación Total</h6>
                        <i class="bi bi-cash-coin fs-4"></i>
                    </div>
                    <h2 class="fw-bold mb-0">${{ number_format($stats['total_revenue'], 0, ',', '.') }}</h2>
                    <p class="small mb-0 opacity-75">Ingresos brutos percibidos</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-danger text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <h6 class="text-uppercase opacity-75 small fw-bold">Deuda con Médicos</h6>
                        <i class="bi bi-bank fs-4"></i>
                    </div>
                    <h2 class="fw-bold mb-0">${{ number_format($stats['total_to_pay'], 0, ',', '.') }}</h2>
                    <p class="small mb-0 opacity-75">Saldos pendientes en billeteras</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-dark text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <h6 class="text-uppercase opacity-75 small fw-bold">Pendientes de Firma</h6>
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <h2 class="fw-bold mb-0">{{ $stats['pending_orders'] }}</h2>
                    <p class="small mb-0 opacity-75">Órdenes pagadas sin emitir</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Detalle por Médico --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2 text-primary"></i>Rendimiento por Profesional</h5>
            <a href="{{ route('admin.payouts.index') }}" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                <i class="bi bi-send-check me-2"></i>Gestionar Pagos
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="small text-muted text-uppercase">
                            <th class="ps-4">Médico</th>
                            <th class="text-center">Firmas</th>
                            <th class="text-end">Venta Bruta</th>
                            <th class="text-end">Honorarios Totales</th>
                            <th class="text-end">Pagado</th>
                            <th class="text-end text-danger fw-bold">Saldo Pendiente</th>
                            <th class="text-end pe-4">Margen Bruto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($doctorReports as $report)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3 bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                        <i class="bi bi-person text-secondary"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold d-block text-dark">{{ $report['name'] }}</span>
                                        <span class="text-muted x-small">ID: #{{ $report['id'] }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">{{ $report['signed_count'] }}</span>
                            </td>
                            <td class="text-end fw-medium">${{ number_format($report['gross_revenue'], 0, ',', '.') }}</td>
                            <td class="text-end text-muted small">${{ number_format($report['historic_earning'], 0, ',', '.') }}</td>
                            <td class="text-end text-muted small">${{ number_format($report['total_paid_out'], 0, ',', '.') }}</td>
                            <td class="text-end">
                                <span class="fw-bold {{ $report['current_balance'] > 0 ? 'text-danger' : 'text-muted' }}">
                                    ${{ number_format($report['current_balance'], 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">
                                    + ${{ number_format($report['net_platform'], 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted small">
                                <i class="bi bi-exclamation-circle d-block fs-2 mb-2"></i>
                                No hay actividad contable registrada.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            <div class="d-flex align-items-center text-muted small">
                <i class="bi bi-info-circle me-2"></i>
                <span>El <strong>Margen Bruto</strong> representa la diferencia entre la venta al paciente y el honorario pactado con el médico.</span>
            </div>
        </div>
    </div>
</div>

<style>
    .x-small { font-size: 0.75rem; }
    .table thead th { border-top: none; font-size: 0.7rem; letter-spacing: 1px; }
    .bg-success-subtle { background-color: rgba(25, 135, 84, 0.1) !important; }
    .bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1) !important; }
</style>
@endsection
