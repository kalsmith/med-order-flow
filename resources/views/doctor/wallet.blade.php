@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Mi Billetera</h2>
            <p class="text-muted small mb-0">Seguimiento en tiempo real de tus honorarios y pagos.</p>
        </div>
        <span class="badge bg-white text-dark shadow-sm border px-3 py-2 rounded-pill">
            <i class="bi bi-person-circle me-2 text-primary"></i>{{ $doctor->user->name }}
        </span>
    </div>

    <div class="row g-4">
        {{-- Card de Saldo Disponible (Negra) --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4 bg-dark text-white p-4 h-100" style="background: linear-gradient(135deg, #1a1d20 0%, #343a40 100%);">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="text-uppercase opacity-50 small fw-bold">Saldo para Retirar</h6>
                            <i class="bi bi-wallet2 fs-4 opacity-50"></i>
                        </div>
                        <h1 class="display-4 fw-bold mb-0">${{ number_format($doctor->balance, 0, ',', '.') }}</h1>

                        @php
                            $pendingAmount = $payoutRequests->where('status', 'pending')->sum('amount');
                            $hasPending = $payoutRequests->where('status', 'pending')->isNotEmpty();
                        @endphp

                        @if($pendingAmount > 0)
                            <div class="mt-3 p-2 rounded-3" style="background: rgba(255,193,7,0.1); border: 1px solid rgba(255,193,7,0.2);">
                                <div class="d-flex justify-content-between align-items-center small">
                                    <span class="text-warning"><i class="bi bi-clock-history me-1"></i> Retiro solicitado:</span>
                                    <span class="fw-bold text-warning">${{ number_format($pendingAmount, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4">
                        @if($doctor->balance > 0 && !$hasPending)
                            <form action="{{ route('admin.payouts.request') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill w-100 py-3 shadow-lg border-0 fw-bold"
                                        onclick="return confirm('¿Confirmas que deseas solicitar el retiro total de tu saldo disponible?')">
                                    <i class="bi bi-cash-stack me-2"></i>Solicitar Retiro de Fondos
                                </button>
                            </form>
                        @elseif($hasPending)
                            <div class="bg-light bg-opacity-10 border border-secondary border-opacity-25 rounded-pill p-3 text-center">
                                <span class="text-white-50 small"><i class="bi bi-info-circle me-2"></i>Tu retiro está siendo validado</span>
                            </div>
                        @else
                            <button class="btn btn-secondary btn-lg rounded-pill w-100 py-3 opacity-50" disabled>
                                Sin saldo disponible
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla de Solicitudes (Lado Derecho) --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-journal-check me-2 text-primary"></i>Gestión de Pagos</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small bg-light">
                                    <th class="ps-4">Fecha</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th class="pe-4 text-end">Documento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payoutRequests as $req)
                                <tr>
                                    <td class="ps-4 text-muted small">{{ $req->created_at->format('d/m/Y') }}</td>
                                    <td class="fw-bold text-dark">${{ number_format($req->amount, 0, ',', '.') }}</td>
                                    <td>
                                        @if($req->status == 'pending')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3">En Revisión</span>
                                        @elseif($req->status == 'paid')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Depositado</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Rechazado</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        @if($req->status == 'paid' && $req->evidence_path)
                                            <a href="{{ route('admin.payouts.download', $req->id) }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm">
                                                <i class="bi bi-file-earmark-pdf"></i> Comprobante
                                            </a>
                                        @else
                                            <span class="text-muted small">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-5 text-muted small">No hay registros de retiro aún.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CARTOLA UNIFICADA (HISTORIAL DE MOVIMIENTOS) --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-arrow-down-up me-2 text-secondary"></i>Detalle de Movimientos</h5>
                    <div class="d-none d-md-flex gap-2">
                        <span class="badge bg-success-subtle text-success rounded-pill fw-normal border border-success-subtle px-3">Verde: Pago Recibido</span>
                        <span class="badge bg-warning-subtle text-warning rounded-pill fw-normal border border-warning-subtle px-3">Amarillo: Retiro en Proceso</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small bg-light">
                                    <th class="ps-4">Fecha y Hora</th>
                                    <th>Tipo de Movimiento</th>
                                    <th>Detalle / Referencia</th>
                                    <th class="text-end pe-4">Monto Bruto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($combinedMovements as $mov)
                                    @if($mov->is_payment)
                                        @php
                                            $isPending = $mov->status == 'pending';
                                            $rowStyle = $isPending ? 'table-warning border-warning' : 'table-success border-success';
                                            $textStyle = $isPending ? 'text-dark' : 'text-success';
                                        @endphp
                                        {{-- FILA DE RETIRO (EGRESO) --}}
                                        <tr class="{{ $rowStyle }} border-start border-4">
                                            <td class="ps-4 small fw-bold">{{ $mov->date_for_sort->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="badge {{ $isPending ? 'bg-warning' : 'bg-success' }} text-white rounded-pill px-3">
                                                    {{ $isPending ? 'RETIRO EN PROCESO' : 'PAGO CANCELADO' }}
                                                </span>
                                            </td>
                                            <td class="{{ $textStyle }} fw-bold">
                                                <i class="bi {{ $isPending ? 'bi-hourglass-split' : 'bi-check2-all' }} me-1"></i>
                                                {{ $isPending ? 'Validando transferencia bancaria' : 'Transferencia enviada con éxito' }}
                                            </td>
                                            <td class="text-end pe-4 fw-bold {{ $textStyle }}">
                                                - ${{ number_format($mov->display_amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @else
                                        {{-- FILA DE FIRMA (INGRESO) --}}
                                        <tr>
                                            <td class="ps-4 small text-muted">{{ $mov->date_for_sort->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($mov->order->type == 'custom')
                                                    <span class="badge bg-info-subtle text-info rounded-pill px-2">Orden Custom</span>
                                                @elseif($mov->order->type == 'multiple')
                                                    <span class="badge rounded-pill px-2" style="background-color: #f3e5f5; color: #7b1fa2;">Orden Múltiple</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2">Orden Standard</span>
                                                @endif
                                            </td>
                                            <td class="text-dark">Paciente: {{ $mov->order->patient->user->name ?? 'N/A' }}</td>
                                            <td class="text-end pe-4 fw-bold text-dark">
                                                + ${{ number_format($mov->display_amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted small">No se registran movimientos en el historial reciente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 py-3 text-center">
                    <p class="text-muted x-small mb-0">
                        <i class="bi bi-info-circle me-1"></i> Este historial muestra los últimos 25 movimientos.
                        Las firmas anteriores a un "Pago Cancelado" ya han sido liquidadas.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .x-small { font-size: 0.75rem; }
    .table-warning { background-color: rgba(255, 193, 7, 0.08) !important; }
    .table-success { background-color: rgba(25, 135, 84, 0.08) !important; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endsection
