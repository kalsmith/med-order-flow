@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Mi Billetera MedOrder</h2>
        <span class="badge bg-white text-dark shadow-sm border px-3 py-2 rounded-pill">
            <i class="bi bi-person-badge me-2"></i>{{ $doctor->user->name }}
        </span>
    </div>

    <div class="row g-4">
        {{-- Card de Saldo Principal --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4 bg-dark text-white p-4 h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="text-uppercase opacity-50 small mb-3">Saldo disponible para retiro</h6>
                        <h1 class="display-4 fw-bold mb-0">${{ number_format($doctor->balance, 0, ',', '.') }}</h1>

                        {{-- Desglose de saldos bloqueados --}}
                        @if($doctor->pending_balance > 0)
                            <div class="mt-3 p-2 rounded-3" style="background: rgba(255,255,255,0.1)">
                                <div class="d-flex justify-content-between small">
                                    <span>En revisión:</span>
                                    <span class="fw-bold">${{ number_format($doctor->pending_balance, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endif
                        <p class="text-muted mt-3 small">Monto neto acumulado por firmas realizadas y no liquidadas.</p>
                    </div>

                    <div class="mt-4">
                        @php
                            $hasPending = $doctor->payoutRequests()->where('status', 'pending')->exists();
                        @endphp

                        @if($doctor->balance > 0 && !$hasPending)
                            <form action="{{ route('admin.payouts.request') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill w-100 py-3 shadow border-0"
                                        onclick="return confirm('¿Confirmas que deseas solicitar el retiro total de tu saldo disponible?')">
                                    <i class="bi bi-send-fill me-2"></i>Solicitar Retiro Total
                                </button>
                            </form>
                        @elseif($hasPending)
                            <button class="btn btn-warning btn-lg rounded-pill w-100 py-3 text-dark fw-bold" disabled>
                                <i class="bi bi-clock-history me-2"></i>Solicitud en proceso...
                            </button>
                        @else
                            <button class="btn btn-secondary btn-lg rounded-pill w-100 py-3" disabled>
                                Sin fondos para retirar
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Resumen de Estados Rápidos --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold">Estado de mis solicitudes</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small">
                                    <th class="ps-4">Fecha</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th class="pe-4 text-end">Comprobante</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($doctor->payoutRequests()->latest()->take(5)->get() as $req)
                                <tr>
                                    <td class="ps-4 text-muted">{{ $req->created_at->format('d/m/Y') }}</td>
                                    <td class="fw-bold text-dark">${{ number_format($req->amount, 0, ',', '.') }}</td>
                                    <td>
                                        @if($req->status == 'pending')
                                            <span class="badge bg-warning-subtle text-warning rounded-pill px-3">En Revisión</span>
                                        @elseif($req->status == 'paid')
                                            <span class="badge bg-success-subtle text-success rounded-pill px-3">Pagado</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Rechazado</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        @if($req->status == 'paid' && $req->evidence_path)
                                            <a href="{{ route('admin.payouts.download', $req) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="bi bi-file-earmark-pdf"></i> Ver Pago
                                            </a>
                                        @elseif($req->status == 'rejected' && $req->admin_notes)
                                            <button type="button" class="btn btn-sm btn-link text-danger p-0" data-bs-toggle="tooltip" title="{{ $req->admin_notes }}">
                                                <i class="bi bi-info-circle"></i> Motivo
                                            </button>
                                        @else
                                            <span class="text-muted small italic">--</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted small">No hay historial de retiros.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detalle de Firmas Recientes --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Detalle de Firmas (Últimas 15)</h5>
                    <span class="text-muted small">Base de cálculo: Standard $1.800 / Custom $2.800</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted small">
                                    <th class="ps-4">Fecha Firma</th>
                                    <th>Tipo de Orden</th>
                                    <th>Paciente</th>
                                    <th class="text-end pe-4">Honorario Bruto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSignatures as $sig)
                                    <tr>
                                        <td class="ps-4">{{ $sig->signed_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($sig->order->type == 'custom')
                                                <span class="badge bg-info-subtle text-info">Custom</span>
                                            @elseif($sig->order->type == 'multiple')
                                                <span class="badge bg-primary-subtle text-primary" style="background-color: #f3e5f5; color: #7b1fa2;">Múltiple</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Standard</span>
                                            @endif
                                        </td>
                                        <td class="small">{{ $sig->order->patient->user->name ?? 'N/A' }}</td>
                                        <td class="text-end pe-4 fw-bold">
                                            ${{ number_format($sig->order->type == 'custom' ? 2800 : 1800, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No hay firmas registradas recientemente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
