@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Gestión de Retiros</h2>
        <div class="badge bg-warning text-dark px-3 py-2">Pendientes: {{ $pendingPayouts->count() }}</div>
    </div>

    {{-- Solicitudes Pendientes --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-clock-history me-2"></i>Solicitudes por Procesar</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Médico</th>
                            <th>Monto</th>
                            <th>Fecha Solicitud</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingPayouts as $payout)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">{{ $payout->doctor->user->name }}</div>
                                <small class="text-muted">RUT: {{ $payout->doctor->rut }}</small>
                            </td>
                            <td class="fw-bold text-dark">${{ number_format($payout->amount, 0, ',', '.') }}</td>
                            <td>{{ $payout->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end pe-4">
                                <button class="btn btn-success btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalPay{{ $payout->id }}">
                                    <i class="bi bi-check-circle me-1"></i> Procesar Pago
                                </button>
                            </td>
                        </tr>

                        {{-- Modal de Procesamiento --}}
                        <div class="modal fade" id="modalPay{{ $payout->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="{{ route('admin.payouts.process', $payout) }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow rounded-4">
                                    @csrf
                                    <div class="modal-header border-0">
                                        <h5 class="modal-title fw-bold">Procesar Pago a {{ $payout->doctor->user->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-info border-0 rounded-4 small">
                                            Monto a transferir: <strong>${{ number_format($payout->amount, 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Comprobante (Imagen o PDF)</label>
                                            <input type="file" name="evidence" class="form-control rounded-3" required accept="image/*,.pdf">
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label fw-bold small">Notas Internas (Opcional)</label>
                                            <textarea name="admin_notes" class="form-control rounded-3" rows="2" placeholder="Ej: Transferencia Banco Estado..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary rounded-pill px-4">Finalizar Pago</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">No hay retiros pendientes.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Historial de Pagos Realizados --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 fw-bold"><i class="bi bi-journal-check me-2"></i>Historial de Liquidaciones</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="text-muted small uppercase">
                        <tr>
                            <th class="ps-4">Médico</th>
                            <th>Monto</th>
                            <th>Fecha Pago</th>
                            <th class="text-center">Comprobante</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historyPayouts as $history)
                        <tr>
                            <td class="ps-4">{{ $history->doctor->user->name }}</td>
                            <td class="fw-medium">${{ number_format($history->amount, 0, ',', '.') }}</td>
                            <td>{{ $history->paid_at->format('d/m/Y') }}</td>
                            <td class="text-center">
                                <a href="{{ Storage::url($history->evidence_path) }}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-circle">
                                    <i class="bi bi-file-earmark-text"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $historyPayouts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
