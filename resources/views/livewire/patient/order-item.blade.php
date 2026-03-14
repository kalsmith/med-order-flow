<div class="card border-0 shadow-sm mb-4 card-order overflow-hidden">
    <div class="card-body p-0">
        <div class="d-flex flex-column flex-md-row">
            <div class="p-4 bg-light text-center d-flex flex-column justify-content-center border-end" style="min-width: 140px;">
                <span class="text-uppercase small fw-bold text-muted mb-2">Orden</span>
                <span class="h5 fw-800 mb-0">#{{ substr($order->id, 0, 8) }}</span>
                <div class="mt-3">
                    @php
                        $statusColors = [
                            'paid' => 'bg-success-subtle text-success',
                            'pending' => 'bg-warning-subtle text-warning',
                            'refund_pending' => 'bg-info-subtle text-info',
                            'refunded' => 'bg-secondary-subtle text-secondary'
                        ];
                        $statusLabels = [
                            'paid' => 'Pagada',
                            'pending' => 'Pendiente',
                            'refund_pending' => 'Reembolso',
                            'refunded' => 'Reembolsada'
                        ];
                    @endphp
                    <span class="badge {{ $statusColors[$order->status] ?? 'bg-light' }} rounded-pill px-3">
                        {{ $statusLabels[$order->status] ?? $order->status }}
                    </span>
                </div>
            </div>

            <div class="p-4 flex-grow-1">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">
                            {{ $order->examType->name ?? ($order->type == 'custom' ? 'Examen Personalizado' : 'Examen Estándar') }}
                        </h5>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-calendar3 me-1"></i> {{ $order->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="text-end">
                        <span class="h5 fw-bold text-primary">${{ number_format($order->amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @if($order->status == 'paid')
                        <button class="btn btn-primary btn-sm rounded-pill px-3">
                            <i class="bi bi-download me-1"></i> Descargar Orden
                        </button>
                    @endif

                    <button class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="bi bi-chat-dots me-1"></i> Ver Chat
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
