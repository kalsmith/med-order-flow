<div wire:poll.15s class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
    <div class="card-header bg-white pt-4 pb-0 border-bottom-0">
        <div class="d-flex justify-content-between align-items-center mb-3 px-2">
            <h5 class="mb-0 text-dark fw-bold">
                <i class="bi bi-clipboard2-pulse text-primary me-2"></i>
                Gestión de Órdenes
            </h5>
            <div class="text-muted small">
                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle fw-medium">
                    <i class="bi bi-dot fs-6"></i> En vivo
                </span>
            </div>
        </div>

        <ul class="nav nav-tabs border-bottom-0 px-2">
            <li class="nav-item">
                <button wire:click="setTab('available')"
                    class="nav-link {{ $tab === 'available' ? 'active fw-bold border-bottom border-primary border-3' : 'text-muted' }} border-0 bg-transparent pb-3">
                    Pendientes de Firma
                </button>
            </li>
            <li class="nav-item">
                <button wire:click="setTab('signed')"
                    class="nav-link {{ $tab === 'signed' ? 'active fw-bold border-bottom border-primary border-3' : 'text-muted' }} border-0 bg-transparent pb-3">
                    Historial y Rechazos
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light-subtle">
                    <tr>
                        <th class="ps-4 py-3 text-uppercase small fw-bold text-muted">Paciente</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted">Examen / Tipo</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted">Fecha</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted">Estado</th>
                        <th class="text-end pe-4 py-3 text-uppercase small fw-bold text-muted">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @php $meId = strval(auth()->user()->doctor->id ?? ''); @endphp

                    @forelse($orders as $order)
                        @php
                            $orderDocId = $order->doctor_id ? strval($order->doctor_id) : null;
                            $isClaimedByOther = $orderDocId && $orderDocId !== $meId && $order->status === 'paid' && $order->claimed_at?->gt(now()->subMinutes(20));
                            $isClaimedByMe = $orderDocId === $meId && $order->status === 'paid';

                            // Una orden está firmada si tiene el timestamp signed_at
                            $isSigned = $order->signed_at !== null;

                            // Lógica de Re-emisión: No está firmada pero tiene prescripciones previas anuladas
                            $isReentry = !$isSigned && $order->prescriptions->contains('status', 'voided');

                            // Lógica de Rechazos y Reembolsos
                            $isRejected = $order->status === 'rejected';
                            $isRefundPending = $order->status === 'refund_pending';
                            $isRefunded = $order->status === 'refunded';
                            $hasBeenRejected = $isRejected || $isRefundPending || $isRefunded;
                        @endphp
                        {{-- Resaltamos la fila si es una re-emisión para captar la atención --}}
                        <tr class="{{ $hasBeenRejected ? 'opacity-75' : '' }} {{ $isReentry ? 'bg-warning-subtle' : '' }}">
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $order->patient->full_name ?? 'N/A' }}</div>
                                <div class="text-muted small">{{ $order->patient->rut ?? '' }}</div>
                            </td>
                            <td>
                                @if($order->type === 'custom')
                                    <span class="badge border" style="background-color: #f3e8ff; color: #6b21a8; border-color: #d8b4fe;">
                                        <i class="bi bi-stars me-1"></i> Especial (Custom)
                                    </span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-medium">
                                        {{ $order->examType->name ?? 'Estándar' }}
                                    </span>
                                @endif

                                {{-- Badge de advertencia para re-emisiones --}}
                                @if($isReentry)
                                    <div class="mt-1">
                                        <span class="badge bg-warning text-dark border border-warning-subtle fw-bold" style="font-size: 0.65rem;">
                                            <i class="bi bi-arrow-counterclockwise"></i> RE-EMISIÓN
                                        </span>
                                    </div>
                                @endif

                                @if($order->interactions_count > 0)
                                    <div class="small text-info mt-1"><i class="bi bi-chat-text me-1"></i> {{ $order->interactions_count }} mensajes</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium text-dark small">{{ $order->created_at->format('d/m/Y') }}</div>
                                <div class="text-muted small" style="font-size: 0.7rem;">{{ $order->created_at->format('H:i') }} hrs</div>
                            </td>
                            <td>
                                @if($isSigned)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-medium">
                                        <i class="bi bi-patch-check-fill me-1"></i> Firmado
                                    </span>
                                @elseif($isRefundPending)
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-medium">
                                        <i class="bi bi-arrow-left-right me-1"></i> Reembolso
                                    </span>
                                @elseif($isRefunded)
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fw-medium">
                                        <i class="bi bi-cash-stack me-1"></i> Reembolsada
                                    </span>
                                @elseif($isRejected)
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-medium" title="{{ $order->rejection_reason }}">
                                        <i class="bi bi-x-circle-fill me-1"></i> Rechazado
                                    </span>
                                @elseif($isClaimedByOther)
                                    <span class="badge bg-light text-muted border border-secondary-subtle fw-normal">
                                        <i class="bi bi-person-fill-lock me-1"></i> En revisión
                                    </span>
                                @elseif($isClaimedByMe)
                                    <span class="badge bg-info-subtle text-info border border-info-subtle fw-medium">
                                        <i class="bi bi-arrow-right-circle-fill me-1"></i> En tu poder
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-medium">
                                        Pendiente
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($isSigned)
                                    <div class="btn-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                                        <a href="{{ route('admin.orders.sign.form', ['order' => $order->id]) }}" class="btn btn-sm btn-white border border-end-0" title="Ver detalles">
                                            <i class="bi bi-eye text-primary"></i>
                                        </a>
                                        <a href="{{ route('admin.orders.pdf', ['order' => $order->id]) }}" target="_blank" class="btn btn-sm btn-white border" title="Descargar PDF">
                                            <i class="bi bi-file-pdf text-danger"></i>
                                        </a>
                                    </div>
                                @elseif($hasBeenRejected)
                                    <a href="{{ route('admin.orders.sign.form', ['order' => $order->id]) }}" class="btn btn-sm btn-outline-secondary px-3 rounded-pill fw-medium">
                                        <i class="bi bi-search me-1"></i> Detalle
                                    </a>
                                @else
                                    @if($isClaimedByOther)
                                        <button class="btn btn-sm btn-light border text-muted" disabled>
                                            <i class="bi bi-lock-fill"></i>
                                        </button>
                                    @else
                                        {{-- El botón cambia de color y texto si es re-emisión --}}
                                        <a href="{{ route('admin.orders.sign.form', ['order' => $order->id]) }}"
                                           class="btn btn-sm {{ $isClaimedByMe ? 'btn-info text-white' : ($isReentry ? 'btn-warning' : 'btn-primary') }} px-3 rounded-pill shadow-sm fw-bold">
                                            <i class="bi {{ $isClaimedByMe ? 'bi-play-fill' : 'bi-vector-pen' }} me-1"></i>
                                            {{ $isClaimedByMe ? 'Continuar' : ($isReentry ? 'Corregir' : 'Atender') }}
                                        </a>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 bg-light-subtle">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-3 opacity-50"></i>
                                <h6 class="text-muted fw-normal">No hay registros en esta sección.</h6>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-center">
        {{ $orders->links() }}
    </div>
</div>
