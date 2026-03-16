@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Contabilidad y Liquidaciones</h2>
        <span class="badge bg-dark px-3 py-2">Corte al: {{ now()->format('d/m/Y') }}</span>
    </div>

    {{-- Widgets Financieros --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white h-100">
                <div class="card-body p-4">
                    <h6 class="text-uppercase opacity-75 small fw-bold">Ingresos Brutos</h6>
                    <h2 class="fw-bold mb-0">${{ number_format($stats['total_revenue'], 0, ',', '.') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-danger text-white h-100">
                <div class="card-body p-4">
                    <h6 class="text-uppercase opacity-75 small fw-bold">Deuda con Médicos</h6>
                    <h2 class="fw-bold mb-0">${{ number_format($stats['total_to_pay'], 0, ',', '.') }}</h2>
                    <small class="opacity-75">Costo fijo: $1.500 por firma</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-dark text-white h-100">
                <div class="card-body p-4">
                    <h6 class="text-uppercase opacity-75 small fw-bold">Órdenes Pendientes</h6>
                    <h2 class="fw-bold mb-0">{{ $stats['pending_orders'] }}</h2>
                    <small class="opacity-75">Pagadas esperando firma</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Liquidación --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 fw-bold"><i class="bi bi-wallet2 me-2"></i>Detalle de Pagos a Médicos</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Médico</th>
                            <th class="text-center">Firmas Realizadas</th>
                            <th class="text-end">Recaudación</th>
                            <th class="text-end text-danger">Honorarios</th>
                            <th class="text-end text-muted small">Comisión Flow (Est.)</th>
                            <th class="text-end pe-4">Utilidad Neta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($doctorReports as $report)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $report['name'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-secondary rounded-pill px-3">{{ $report['signed_count'] }}</span>
                            </td>
                            <td class="text-end">${{ number_format($report['gross_revenue'], 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-danger">-${{ number_format($report['payout_doctor'], 0, ',', '.') }}</td>
                            <td class="text-end text-muted small">-${{ number_format($report['flow_fees'], 0, ',', '.') }}</td>
                            <td class="text-end pe-4">
                                <span class="fw-bold text-success" style="font-size: 1.1rem;">
                                    ${{ number_format($report['net_platform'], 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No hay registros para mostrar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
