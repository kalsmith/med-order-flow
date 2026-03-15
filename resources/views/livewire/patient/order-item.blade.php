@php
    // Definimos el color del acento lateral según el estado de la orden
    $accentClass = [
        'paid' => 'border-start-success',
        'refund_pending' => 'border-start-info',
        'refunded' => 'border-start-secondary',
        'pending' => 'border-start-warning'
    ][$order->status] ?? 'border-start-primary';

    $statusConfig = [
        'paid' => ['label' => 'Pagada', 'class' => 'bg-success text-white'],
        'pending' => ['label' => 'Pendiente', 'class' => 'bg-warning text-dark'],
        'refund_pending' => ['label' => 'En Reembolso', 'class' => 'bg-info text-white'],
        'refunded' => ['label' => 'Reembolsada', 'class' => 'bg-secondary text-white']
    ];
    $currentStatus = $statusConfig[$order->status] ?? ['label' => $order->status, 'class' => 'bg-light text-dark'];
@endphp

<div class="card mb-4 overflow-hidden rounded-4 card-order-patient shadow-sm border {{ $accentClass }}"
     style="border-width: 1px 1px 1px 5px !important; border-style: solid !important;">
    <div class="card-body p-0">
        <div class="row g-0">
            <div class="col-md-3 bg-light border-end d-flex flex-column p-4 justify-content-between align-items-center">
                <div class="text-center w-100">
                    <div class="mb-3">
                        <span class="d-block text-uppercase tracking-wider small fw-bold text-muted mb-1" style="font-size: 0.65rem;">ID Seguimiento</span>
                        <code class="h6 fw-bold text-dark bg-white px-2 py-1 rounded border shadow-sm">#{{ substr($order->id, 0, 8) }}</code>
                    </div>

                    @if($order->activePrescription)
                    <div class="py-2 px-3 rounded-3 bg-white border shadow-sm mb-3">
                        <span class="d-block text-uppercase small fw-bold text-primary mb-1" style="font-size: 0.6rem;">Folio Médico</span>
                        <span class="h5 fw-800 text-dark mb-0">{{ $order->activePrescription->correlative_number }}</span>
                    </div>
                    @endif
                </div>

                <div class="w-100 mt-auto">
                    <span class="badge {{ $currentStatus['class'] }} w-100 py-2 rounded-pill shadow-sm">
                        {{ $currentStatus['label'] }}
                    </span>
                </div>
            </div>

            <div class="col-md-9 p-4 bg-white">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h4 class="fw-800 text-dark mb-0">
                                {{ $order->examType->name ?? ($order->type == 'custom' ? 'Examen Personalizado' : 'Examen Estándar') }}
                            </h4>
                            @if($order->type == 'custom')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1">Custom</span>
                            @endif
                        </div>
                        <p class="text-muted small mb-0 d-flex align-items-center">
                            <i class="bi bi-calendar3 me-2 text-primary"></i> {{ $order->created_at->format('d M, Y • H:i') }}
                        </p>
                    </div>
                    <div class="text-end">
                        <div class="display-6 fw-bold text-primary" style="font-size: 1.5rem;">
                            ${{ number_format($order->amount, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <hr class="my-4 opacity-10">

                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                    <div class="status-messages">
                        @if($isRefunded)
                            <div class="d-flex align-items-center text-danger fw-600 small">
                                <span class="pulse-red me-2"></span> Proceso detenido por reembolso
                            </div>
                        @elseif($isProcessing)
                            <div class="d-flex align-items-center text-primary fw-600 small">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                Médico revisando tu solicitud...
                            </div>
                        @elseif($waitingContact)
                            <div class="d-flex align-items-center text-muted small italic">
                                <i class="bi bi-clock-history me-2"></i> Pendiente de asignación médica
                            </div>
                        @endif
                    </div>

                    <div class="actions-group d-flex gap-2 w-100 w-sm-auto">
                        @if($canDownload)
                            <a href="#" class="btn btn-primary px-4 py-2 rounded-4 shadow-sm d-flex align-items-center">
                                <i class="bi bi-file-earmark-arrow-down-fill me-2 fs-5"></i>
                                Descargar Orden
                            </a>
                        @endif

                        @if($canShowChat)
                            <button class="btn btn-outline-info px-4 py-2 rounded-4 d-flex align-items-center fw-bold"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#chat-collapse-{{ $order->id }}">
                                <i class="bi bi-chat-text-fill me-2 fs-5"></i>
                                Hablar con Médico
                            </button>
                        @endif
                    </div>
                </div>

                @if($canShowChat)
                    <div class="collapse mt-4" id="chat-collapse-{{ $order->id }}" wire:ignore>
                        <div class="border-top pt-4 mt-2">
                             @livewire('patient.order-chat', ['order' => $order], key('chat-'.$order->id))
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
