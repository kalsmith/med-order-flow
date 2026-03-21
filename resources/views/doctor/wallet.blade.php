@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Mi Billetera</h2>
            <p class="text-muted small mb-0">Gestiona tus honorarios y solicitudes de retiro.</p>
        </div>
        <span class="badge bg-white text-dark shadow-sm border px-3 py-2 rounded-pill">
            <i class="bi bi-person-circle me-2 text-primary"></i>{{ $doctor->user->name }}
        </span>
    </div>

    <div class="row g-4">
        {{-- Card de Saldo Principal --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4 bg-dark text-white p-4 h-100" style="background: linear-gradient(135deg, #212529 0%, #343a40 100%);">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="text-uppercase opacity-50 small fw-bold">Saldo Disponible</h6>
                            <i class="bi bi-wallet2 fs-4 opacity-50"></i>
                        </div>
                        <h1 class="display-4 fw-bold mb-0">${{ number_format($doctor->balance, 0, ',', '.') }}</h1>

                        {{-- Indicador de saldo en proceso --}}
                        @php
                            $pendingAmount = $payoutRequests->where('status', 'pending')->sum('amount');
                        @endphp

                        @if($pendingAmount > 0)
                            <div class="mt-3 p-2 rounded-3" style="background: rgba(255,193,7,0.15); border: 1px solid rgba(255,193,7,0.3);">
                                <div class="d-flex justify-content-between align-items-center small">
                                    <span class="text-warning"><i class="bi bi-hourglass-split me-1"></i> Retiro en proceso:</span>
                                    <span class="fw-bold text-warning">${{ number_format($pendingAmount, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4">
                        @php
                            $hasPending = $payoutRequests->where('status', 'pending')->exists();
                        @endphp

                        @if($doctor->balance > 0 && !$hasPending)
                            <form action="{{ route('admin.payouts.request') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill w-100 py-3 shadow-lg border-0 fw-bold"
                                        onclick="return confirm('¿Confirmas que deseas solicitar el retiro total de tu saldo disponible?')">
                                    <i class="bi bi-cash-stack me-2"></i>Solicitar Retiro Total
                                </button>
                            </form>
                        @elseif($hasPending)
                            <div class="bg-light bg-opacity-10 border border-secondary border-opacity-25 rounded-pill p-3 text-center">
                                <span class="text-white-50 small"><i class="bi bi-info-circle me-2"></i>Tienes un retiro en revisión</span>
                            </div>
                        @else
                            <button class="btn btn-secondary btn-lg rounded-pill w-100 py-3 opacity-50" disabled>
                                Sin fondos disponibles
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Estado de Solicitudes (Egresos) --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-arrow-up-right-circle me-2 text-danger"></i>Últimos Retiros</h5>
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
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3">Pendiente</span>
                                        @elseif($req->status == 'paid')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Pagado</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Rechazado</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        @if($req->status == 'paid' && $req->evidence_path)
                                            <a href="{{ Storage::url($req->evidence_path) }}" target="_blank" class="btn btn-sm btn-light border rounded-pill px-3">
                                                <i class="bi bi-file-earmark-pdf text-danger"></i> Comprobante
                                            </a>
                                        @elseif($req->status == 'rejected')
                                            <i class="bi bi-exclamation-circle text-danger" title="{{ $req->admin_notes }}" data-bs-toggle="tooltip"></i>
                                        @else
                                            <span class="text-muted small">--</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-25"></i>
                                        No has solicitado retiros aún.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detalle de Firmas (Ingresos) --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-plus-circle me-2 text-success"></i>Detalle de Ingresos (Firmas)</h5>
                    <div class="d-none d-md-block">
                        <span class="badge bg-light text-dark border rounded-pill fw-normal px-3">Standard: $1.800</span>
                        <span class="badge bg-light text-dark border rounded-pill fw-normal px-3 ms-2">Custom/Múltiple: $2.800</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small bg-light">
                                    <th class="ps-4">Fecha y Hora</th>
                                    <th>Tipo de Orden</th>
                                    <th>Paciente</th>
                                    <th class="text-end pe-4">Honorario Bruto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSignatures as $sig)
                                    <tr>
                                        <td class="ps-4 small">{{ $sig->signed_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($sig->order->type == 'custom')
                                                <span class="badge bg-info-subtle text-info rounded-pill px-3">Custom</span>
                                            @elseif($sig->order->type == 'multiple')
                                                <span class="badge rounded-pill px-3" style="background-color: #f3e5f5; color: #7b1fa2;">Múltiple</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">Standard</span>
                                            @endif
                                        </td>
                                        <td class="text-dark">{{ $sig->order->patient->user->name ?? 'N/A' }}</td>
                                        <td class="text-end pe-4 fw-bold">
                                            ${{ number_format(in_array($sig->order->type, ['custom', 'multiple']) ? 2800 : 1800, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">No hay registros de firmas recientes.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 py-3 text-center">
                    <p class="text-muted small mb-0">Solo se muestran las últimas 15 firmas. El saldo acumulado incluye todas las firmas no pagadas.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
@endsection
