<div wire:poll.15s class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
    <div class="card-header bg-white pt-4 pb-0 border-bottom-0">
        <div class="d-flex justify-content-between align-items-center mb-3 px-2">
            <h5 class="mb-0 text-dark fw-bold">
                <i class="bi bi-clipboard2-pulse text-primary me-2"></i>
                Gestión de Órdenes Médicas
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
                    <i class="bi bi- megaphone me-1"></i> Nuevas Pendientes
                </button>
            </li>
            <li class="nav-item">
                <button wire:click="setTab('reentry')"
                    class="nav-link {{ $tab === 'reentry' ? 'active fw-bold border-bottom border-warning border-3' : 'text-muted' }} border-0 bg-transparent pb-3 position-relative">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Por Re-firmar
                    @php
                        // Opcional: Esto asumiendo que pasas un conteo desde el componente
                        $reentryCount = \App\Models\Order::where('status', 'paid')->whereNull('signed_at')->whereHas('prescriptions', fn($q) => $q->where('status', 'voided'))->count();
                    @endphp
                    @if($reentryCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                            {{ $reentryCount }}
                        </span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button wire:click="setTab('signed')"
                    class="nav-link {{ $tab === 'signed' ? 'active fw-bold border-bottom border-secondary border-3' : 'text-muted' }} border-0 bg-transparent pb-3">
                    <i class="bi bi-archive me-1"></i> Historial y Rechazos
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light-subtle">
                    <tr>
                        <th class="ps-4 py-3 text-uppercase small fw-bold text-muted" style="letter-spacing: 0.5px;">Paciente</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted" style="letter-spacing: 0.5px;">Examen / Tipo</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted" style="letter-spacing: 0.5px;">Fecha Registro</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted" style="letter-spacing: 0.5px;">Estado Actual</th>
                        <th class="text-end pe-4 py-3 text-uppercase small fw-bold text-muted" style="letter-spacing: 0.5px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @php $meId = strval(auth()->user()->doctor->id ?? ''); @endphp

                    @forelse($orders as $order)
                        @php
                            $orderDocId = $order->doctor_id ? strval($order->doctor_id) : null;
                            $isClaimedByOther = $orderDocId && $orderDocId !== $meId && $order->status === 'paid' && $order->claimed_at?->gt(now()->subMinutes(20));
                            $isClaimedByMe = $orderDocId === $meId && $order->status === 'paid';
                            $isSigned = $order->signed_at !== null;

                            // Lógica de Re-emisión detectada por historial de prescripciones
                            $isReentry = !$isSigned && $order->prescriptions->contains('status', 'voided');

                            // Estados de fin de ciclo
                            $isRejected = $order->status === 'rejected';
                            $isRefundPending = $order->status === 'refund_pending';
                            $isRefunded = $order->status === 'refunded';
                            $hasBeenRejected = $isRejected || $isRefundPending || $isRefunded;
                        @endphp

                        <tr class="{{ $hasBeenRejected ? 'opacity-75' : '' }} {{ $isReentry ? 'bg-warning-subtle bg-opacity-10' : '' }}">
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3 bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                        {{ substr($order->patient->full_name ?? 'N', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0">{{ $order->patient->full_name ?? 'N/A' }}</div>
                                        <div class="text-muted small">{{ $order->patient->rut ?? 'Sin RUT' }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3">
                                @if($order->type === 'custom')
                                    <span class="badge rounded-pill" style="background-color: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe;">
                                        <i class="bi bi-stars me-1"></i> Especial
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle fw-medium">
                                        {{ $order->examType->name ?? 'Estándar' }}
                                    </span>
                                @endif

                                @if($isReentry)
                                    <div class="mt-1">
                                        <span class="badge bg-warning text-dark border border-warning-subtle fw-bold" style="font-size: 0.65rem;">
                                            <i class="bi bi-exclamation-triangle-fill"></i> RE-FIRMA REQUERIDA
                                        </span>
                                    </div>
                                @endif

                                @if($order->interactions_count > 0)
                                    <div class="small text-info mt-1 fw-medium">
                                        <i class="bi bi-chat-left-dots-fill me-1"></i> {{ $order->interactions_count }} interacción(es)
                                    </div>
                                @endif
                            </td>

                            <td class="py-3">
                                <div class="text-dark fw-medium small">{{ $order->created_at->format('d M, Y') }}</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">{{ $order->created_at->format('H:i') }} hrs</div>
                            </td>

                            <td class="py-3">
                                @if($isSigned)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3">
                                        <i class="bi bi-check2-all me-1"></i> Firmado
                                    </span>
                                @elseif($isRefundPending)
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3">
                                        <i class="bi bi-hourglass-split me-1"></i> Reembolso
                                    </span>
                                @elseif($isRefunded)
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3">
                                        <i class="bi bi-cash-stack me-1"></i> Reembolsada
                                    </span>
                                @elseif($isRejected)
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3" title="{{ $order->rejection_reason }}">
                                        <i class="bi bi-x-octagon me-1"></i> Rechazado
                                    </span>
                                @elseif($isClaimedByOther)
                                    <span class="badge bg-light text-muted border border-secondary-subtle px-3 fw-normal">
                                        <i class="bi bi-lock-fill me-1"></i> En revisión
                                    </span>
                                @elseif($isClaimedByMe)
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-3 fw-medium anim-pulse">
                                        <i class="bi bi-pencil-square me-1"></i> Editando
                                    </span>
                                @else
                                    <span class="badge {{ $isReentry ? 'bg-warning-subtle text-warning' : 'bg-light text-dark' }} border px-3">
                                        {{ $isReentry ? 'Pendiente Corrección' : 'Esperando Médico' }}
                                    </span>
                                @endif
                            </td>

                            <td class="text-end pe-4 py-3">
                                @if($isSigned)
                                    <div class="btn-group shadow-sm bg-white" style="border-radius: 8px;">
                                        <a href="{{ route('admin.orders.sign.form', ['order' => $order->id]) }}" class="btn btn-sm btn-outline-light border text-primary" title="Ver Detalles">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.orders.pdf', ['order' => $order->id]) }}" target="_blank" class="btn btn-sm btn-outline-light border text-danger" title="Ver PDF">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                    </div>
                                @elseif($hasBeenRejected)
                                    <a href="{{ route('admin.orders.sign.form', ['order' => $order->id]) }}" class="btn btn-sm btn-outline-secondary px-3 rounded-pill">
                                        Ver Motivo
                                    </a>
                                @elseif($isClaimedByOther)
                                    <button class="btn btn-sm btn-light border text-muted opacity-50" disabled>
                                        <i class="bi bi-lock"></i> Bloqueado
                                    </button>
                                @else
                                    <a href="{{ route('admin.orders.sign.form', ['order' => $order->id]) }}"
                                       class="btn btn-sm {{ $isClaimedByMe ? 'btn-info text-white' : ($isReentry ? 'btn-warning' : 'btn-primary') }} px-4 rounded-pill shadow-sm fw-bold">
                                        <i class="bi {{ $isClaimedByMe ? 'bi-play-circle-fill' : ($isReentry ? 'bi-arrow-repeat' : 'bi-pen-fill') }} me-1"></i>
                                        {{ $isClaimedByMe ? 'Continuar' : ($isReentry ? 'Corregir' : 'Atender') }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 bg-light-subtle">
                                <div class="py-4">
                                    <i class="bi bi-clipboard-x fs-1 text-muted opacity-25"></i>
                                    <h6 class="text-muted fw-normal mt-3">No hay órdenes en la categoría <strong>"{{ $tab }}"</strong></h6>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer bg-white border-top-0 py-4 d-flex justify-content-center">
        {{ $orders->links() }}
    </div>
</div>

<style>
    .nav-tabs .nav-link.active {
        color: #0d6efd !important;
    }
    .anim-pulse {
        animation: pulse-red 2s infinite;
    }
    @keyframes pulse-red {
        0% { opacity: 1; }
        50% { opacity: 0.6; }
        100% { opacity: 1; }
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,0.01) !important;
    }
    .btn-white {
        background: white;
    }
</style>
