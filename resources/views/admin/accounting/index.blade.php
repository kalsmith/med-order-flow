@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Contabilidad y Liquidaciones</h2>
        <div class="text-end">
            <span class="badge bg-dark px-3 py-2">Corte al: {{ now()->format('d/m/Y') }}</span>
        </div>
    </div>

    {{-- Widgets Financieros --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white">
                <div class="card-body p-4">
                    <h6 class="text-uppercase opacity-75 small fw-bold">Recaudación Total (Ventas)</h6>
                    <h2 class="fw-bold mb-0">${{ number_format($stats['total_revenue'], 0, ',', '.') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-danger text-white">
                <div class="card-body p-4">
                    <h6 class="text-uppercase opacity-75 small fw-bold">Deuda Actual (Billeteras)</h6>
                    <h2 class="fw-bold mb-0">${{ number_format($stats['total_to_pay'], 0, ',', '.') }}</h2>
                    <small class="opacity-75">Suma de saldos disponibles para retiro</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-dark text-white">
                <div class="card-body p-4">
                    <h6 class="text-uppercase opacity-75 small fw-bold">Órdenes por Firmar</h6>
                    <h2 class="fw-bold mb-0">{{ $stats['pending_orders'] }}</h2>
                    <small class="opacity-75">Potencial honorario: ${{ number_format($stats['pending_orders'] * 1500, 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Detalle por Médico --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2"></i>Rendimiento y Saldos</h5>
            <a href="{{ route('admin.payouts.index') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                <i class="bi bi-cash-stack me-1"></i> Gestionar Pagos
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="small text-muted">
                            <th class="ps-4">Médico</th>
                            <th class="text-center">Firmas Totales</th>
                            <th class="text-end">Histórico Generado</th>
                            <th class="text-end">Ya Pagado</th>
                            <th class="text-end text-danger fw-bold">Saldo x Pagar (Hoy)</th>
                            <th class="text-end pe-4">Utilidad Neta MedOrder</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($doctorReports as $report)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold d-block">{{ $report['name'] }}</span>
                                <span class="text-muted small">ID: #{{ $report['id'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary rounded-pill">{{ $report['signed_count'] }}</span>
                            </td>
                            <td class="text-end">${{ number_format($report['historic_earning'], 0, ',', '.') }}</td>
                            <td class="text-end text-muted">${{ number_format($report['total_paid_out'], 0, ',', '.') }}</td>
                            <td class="text-end text-danger fw-bold">
                                ${{ number_format($report['current_balance'], 0, ',', '.') }}
                            </td>
                            <td class="text-end pe-4">
                                <span class="text-success fw-bold">
                                    ${{ number_format($report['net_platform'], 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No hay actividad registrada.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
