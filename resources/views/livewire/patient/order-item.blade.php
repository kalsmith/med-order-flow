@php
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

{{-- Poll de 15 segundos --}}
<div wire:poll.15s class="card mb-4 overflow-hidden rounded-4 card-order-patient shadow-sm border {{ $accentClass }}"
     style="border-width: 1px 1px 1px 5px !important; border-style: solid !important; transition: all 0.3s ease;">

    <div class="card-body p-0">
        <div class="row g-0">
            <div class="col-md-3 bg-light border-end d-flex flex-column p-4 justify-content-between align-items-center text-center">
                <div class="w-100">
                    <div class="mb-3">
                        <span class="d-block text-uppercase tracking-wider small fw-bold text-muted mb-1" style="font-size: 0.65rem;">ID Seguimiento</span>
                        <code class="h6 fw-bold text-dark bg-white px-2 py-1 rounded border shadow-sm">#{{ substr($order->id, 0, 8) }}</code>
                    </div>

                    @if($order->activePrescription)
                    <div class="py-2 px-3 rounded-3 bg-white border shadow-sm mb-3 animate__animated animate__fadeIn">
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

                {{-- ALERTA DE CHAT ACTIVO --}}
                @if($canShowChat)
                    <div class="alert alert-info border-0 shadow-sm rounded-3 d-flex align-items-center py-2 px-3 mb-3 animate__animated animate__bounceIn" style="background-color: #e7f3ff;">
                        <i class="bi bi-chat-dots-fill text-primary me-3 fs-4"></i>
                        <div class="flex-grow-1">
                            <p class="mb-0 small fw-bold text-dark">El médico ha iniciado una conversación.</p>
                            <p class="mb-0 x-small text-muted" style="font-size: 0.75rem;">Revisa las indicaciones o responde sus dudas.</p>
                        </div>
                        <button class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-sm"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#chat-collapse-{{ $order->id }}">
                            Ver Mensajes
                        </button>
                    </div>
                @endif

                <hr class="my-4 opacity-10">

                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                    <div class="status-messages">
                        @if($order->status === 'refunded')
                            <div class="d-flex align-items-center text-danger fw-600 small">
                                <span class="pulse-red me-2"></span> Proceso detenido por reembolso
                            </div>
                        @elseif($order->status === 'paid' && !$order->activePrescription)
                            <div class="d-flex align-items-center text-primary fw-600 small">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                Médico revisando tu solicitud...
                            </div>
                        @elseif($order->status === 'pending')
                            <div class="d-flex align-items-center text-muted small italic">
                                <i class="bi bi-clock-history me-2"></i> Pendiente de pago / asignación
                            </div>
                        @endif
                    </div>

                    <div class="actions-group d-flex gap-2 w-100 w-sm-auto">
                        {{-- Solo habilitamos descarga si está pagada Y tiene la receta generada --}}
                        @if($order->status === 'paid' && $order->activePrescription)
                            <a href="{{ route('orders.download', $order->id) }}"
                               target="_blank"
                               class="btn btn-primary px-4 py-2 rounded-4 shadow-sm d-flex align-items-center animate__animated animate__fadeInUp">
                                <i class="bi bi-file-earmark-arrow-down-fill me-2 fs-5"></i>
                                Descargar Orden
                            </a>
                        @elseif($order->status === 'pending')
                            <a href="{{ route('checkout.process', $order->id) }}" class="btn btn-warning px-4 py-2 rounded-4 shadow-sm fw-bold">
                                Pagar ahora
                            </a>
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
