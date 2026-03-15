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


{{-- ... (cabecera de la card igual) ... --}}

<div class="d-flex flex-wrap gap-2">
    {{-- BOTÓN DESCARGAR: Solo si está firmada --}}
    @if($canDownload)
        <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Descargar Orden
        </button>

    {{-- PROCESANDO FIRMA: Pagada, pero aún no firmada ni reembolsada --}}
    @elseif($isProcessing)
        <span class="badge bg-light text-primary border py-2 px-3 rounded-pill">
            <i class="bi bi-hourglass-split me-1"></i> Procesando firma médica
        </span>
    @endif

    {{-- CHAT: Solo si el doctor ya habló --}}
    @if($canShowChat)
        <button class="btn btn-info btn-sm rounded-pill px-3 text-white shadow-sm"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#chat-collapse-{{ $order->id }}">
            <i class="bi bi-chat-dots-fill me-1"></i> Consultar al Médico
        </button>

    {{-- ESPERANDO CONTACTO: Solo si es custom, no hay chat aún, no está firmada y no es reembolso --}}
    @elseif($waitingContact)
        <span class="badge bg-light text-muted border py-2 px-3 rounded-pill">
            <i class="bi bi-clock me-1"></i> Esperando contacto del médico
        </span>
    @endif

    {{-- REEMBOLSO: Mensaje informativo si está en ese estado --}}
    @if($isRefunded)
        <span class="badge bg-light text-danger border py-2 px-3 rounded-pill">
            <i class="bi bi-info-circle me-1"></i> Proceso médico detenido por reembolso
        </span>
    @endif
</div>




                @if($canShowChat)
                    <div class="collapse mt-3" id="chat-collapse-{{ $order->id }}" wire:ignore>
                        <div class="card card-body border-0 bg-light p-0 overflow-hidden rounded-4">
                            @livewire('patient.order-chat', ['order' => $order], key('chat-'.$order->id))
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
