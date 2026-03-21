@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0 text-dark">Mi Billetera</h2>
            <p class="text-muted small mb-0">Seguimiento en tiempo real de tus honorarios y liquidaciones.</p>
        </div>
        <span class="badge bg-white text-dark shadow-sm border px-3 py-2 rounded-pill">
            <i class="bi bi-person-circle me-2 text-primary"></i>{{ $doctor->user->name }}
        </span>
    </div>

    <div class="row g-4">
        {{-- Card de Saldo Disponible (Negra) --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4 bg-dark text-white p-4 h-100" style="background: linear-gradient(135deg, #1a1d20 0%, #343a40 100%);">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="text-uppercase opacity-50 small fw-bold tracking-wider">Saldo Disponible</h6>
                            <i class="bi bi-wallet2 fs-4 opacity-50"></i>
                        </div>
                        <h1 class="display-4 fw-bold mb-0">${{ number_format($doctor->balance, 0, ',', '.') }}</h1>

                        @php
                            $pendingReq = $payoutRequests->where('status', 'pending')->first();
                            $pendingAmount = $pendingReq ? $pendingReq->amount : 0;
                        @endphp

                        @if($pendingAmount > 0)
                            <div class="mt-3 p-3 rounded-3" style="background: rgba(255,193,7,0.15); border: 1px solid rgba(255,193,7,0.3);">
                                <div class="d-flex justify-content-between align-items-center small">
                                    <span class="text-warning fw-bold"><i class="bi bi-clock-history me-1"></i> Retiro en proceso:</span>
                                    <span class="fw-bold text-warning">${{ number_format($pendingAmount, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4">
                        @if($doctor->balance > 0 && !$pendingReq)
                            <form action="{{ route('admin.payouts.request') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill w-100 py-3 shadow-lg border-0 fw-bold transition-all"
                                        onclick="return confirm('¿Confirmas que deseas solicitar el retiro total de tu saldo disponible?')">
                                    <i class="bi bi-cash-stack me-2"></i>Solicitar Retiro de Fondos
                                </button>
                            </form>
                        @elseif($pendingReq)
                            <div class="bg-white bg-opacity-10 border border-warning border-opacity-25 rounded-pill p-3 text-center">
                                <span class="text-warning small fw-bold">
                                    <i class="bi bi-hourglass-split me-2 animate-spin"></i>Solicitud de ${{ number_format($pendingAmount, 0, ',', '.') }} en revisión
                                </span>
                            </div>
                        @else
                            <button class="btn btn-secondary btn-lg rounded-pill w-100 py-3 opacity-50" disabled>
                                <i class="bi bi-slash-circle me-2"></i>Sin saldo para retirar
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Mini Tabla de Estados de Solicitud --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-journal-check me-2 text-primary"></i>Historial de Retiros</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small bg-light">
                                    <th class="ps-4">Fecha</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th class="pe-4 text-end">Acción</th>
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
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Pagado</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Rechazado</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        @if($req->status == 'paid' && $req->evidence_path)
                                            <a href="{{ route('admin.payouts.download', $req->id) }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm transition-all">
                                                <i class="bi bi-file-earmark-pdf"></i> Comprobante
                                            </a>
                                        @else
                                            <span class="text-muted x-small italic">Pendiente de pago</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-5 text-muted small">No has solicitado retiros todavía.</td></tr>
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
                    <h5 class="mb-0 fw-bold"><i class="bi bi-arrow-down-up me-2 text-secondary"></i>Cartola Detallada de Movimientos</h5>
                    <div class="d-none d-md-flex gap-2">
                        <span class="badge bg-success-subtle text-success rounded-pill fw-normal border border-success-subtle px-3">Ingreso (Firma)</span>
                        <span class="badge bg-dark-subtle text-dark rounded-pill fw-normal border border-dark-subtle px-3">Egreso (Retiro)</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr class="text-muted small bg-light">
                                    <th class="ps-4">Fecha y Hora</th>
                                    <th>Concepto</th>
                                    <th>Referencia</th>
                                    <th class="text-end pe-4">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($combinedMovements as $mov)
                                    @if($mov->is_payment)
                                        {{-- FILA DE RETIRO (EGRESO) --}}
                                        @php
                                            $isPending = $mov->status == 'pending';
                                            $rowColor = $isPending ? 'bg-warning-light' : 'bg-light text-muted';
                                        @endphp
                                        <tr class="{{ $rowColor }} border-start border-4 {{ $isPending ? 'border-warning' : 'border-secondary' }}">
                                            <td class="ps-4 small fw-bold">{{ $mov->date_for_sort->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="badge {{ $isPending ? 'bg-warning text-dark' : 'bg-secondary' }} rounded-pill px-3">
                                                    {{ $isPending ? 'RETIRO SOLICITADO' : 'PAGO COMPLETADO' }}
                                                </span>
                                            </td>
                                            <td class="small">
                                                <i class="bi bi-bank me-1"></i> {{ $isPending ? 'Pendiente de transferencia' : 'Transferido a su cuenta' }}
                                            </td>
                                            <td class="text-end pe-4 fw-bold">
                                                - ${{ number_format($mov->display_amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @else
                                        {{-- FILA DE FIRMA (INGRESO) --}}
                                        <tr>
                                            <td class="ps-4 small text-muted">{{ $mov->date_for_sort->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if(isset($mov->order) && $mov->order->type == 'custom')
                                                    <span class="badge bg-info-subtle text-info rounded-pill px-2">Firma Custom</span>
                                                @elseif(isset($mov->order) && $mov->order->type == 'multiple')
                                                    <span class="badge rounded-pill px-2" style="background-color: #f3e5f5; color: #7b1fa2;">Firma Múltiple</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2">Firma Standard</span>
                                                @endif
                                            </td>
                                            <td class="text-dark small">Paciente: {{ $mov->order->patient->user->name ?? 'N/A' }}</td>
                                            <td class="text-end pe-4 fw-bold text-success">
                                                + ${{ number_format($mov->display_amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">No hay movimientos registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 py-3">
                    <p class="text-muted x-small mb-0 text-center">
                        <i class="bi bi-shield-check me-1"></i> Los movimientos marcados en amarillo están bloqueados hasta que se confirme el depósito en su cuenta bancaria.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .x-small { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.05em; }
    .bg-warning-light { background-color: rgba(255, 193, 7, 0.05) !important; }
    .transition-all { transition: all 0.3s ease; }
    .transition-all:hover { transform: translateY(-2px); filter: brightness(1.1); }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .animate-spin {
        display: inline-block;
        animation: spin 2s linear infinite;
    }
</style>
@endsection
