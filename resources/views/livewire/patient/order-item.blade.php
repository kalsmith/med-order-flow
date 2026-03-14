{{-- IMPORTANTE: El wire:key es OBLIGATORIO para que Livewire no pierda el UUID --}}
<div wire:key="order-item-{{ $order->id }}" wire:poll.300s class="card card-order border-0 shadow-sm mb-3" style="border-radius: 16px;">
    <div class="card-body p-4">
        <div class="row align-items-center">

            <div class="col-lg-7">
                <div class="d-flex align-items-center mb-3 flex-wrap gap-2">
                    <span class="badge bg-light text-dark border px-3 py-2">
                        ID: {{ strtoupper(substr($order->id, 0, 8)) }}
                    </span>
                    <span class="text-muted small mx-2">
                        <i class="bi bi-calendar3 me-1"></i>
                        {{-- Carbon manejará la conversión de zona horaria --}}
                        {{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : 'Sin fecha' }}
                    </span>
                    <span class="badge bg-primary-subtle text-primary text-uppercase px-3">
                        <i class="bi bi-person-circle me-1"></i> {{ $order->patient->full_name ?? 'PACIENTE' }}
                    </span>
                </div>

                <h2 class="fw-800 text-dark mb-0" style="letter-spacing: -1.2px; font-size: 1.85rem;">
                    {{ $order->display_name }}
                </h2>

                @php
                    $isSigned = $activePrescription && $activePrescription->status === 'signed';
                    $isWaitingDoctor = $order->status === 'paid' && !$isSigned;
                @endphp

                @if($isWaitingDoctor)
                    <div class="mt-4 p-3 info-box bg-light d-inline-flex align-items-center rounded-3 border">
                        <div class="spinner-border spinner-border-sm text-primary me-3" role="status"></div>
                        <div>
                            <span class="d-block fw-bold text-dark small">Médico revisando su solicitud</span>
                            <span class="text-muted d-block" style="font-size: 0.75rem;">Tiempo estimado: 2 a 24 horas.</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-5 mt-4 mt-lg-0 text-lg-end">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-end gap-4">

                    <div class="order-lg-1">
                        @php
                            $statusConfig = [
                                'pending'        => ['class' => 'bg-warning-subtle text-warning-emphasis', 'label' => 'PENDIENTE PAGO'],
                                'paid'           => ['class' => 'bg-info-subtle text-info-emphasis', 'label' => 'EN PROCESO'],
                                'refund_pending' => ['class' => 'bg-dark text-white', 'label' => 'REEMBOLSO PENDIENTE'],
                                'refunded'       => ['class' => 'bg-secondary-subtle text-secondary', 'label' => 'REEMBOLSADA'],
                                'rejected'       => ['class' => 'bg-danger-subtle text-danger', 'label' => 'RECHAZADA'],
                            ];
                            $label = $isSigned ? 'LISTA PARA DESCARGA' : ($statusConfig[$order->status]['label'] ?? strtoupper($order->status));
                            $class = $isSigned ? 'bg-success-subtle text-success' : ($statusConfig[$order->status]['class'] ?? 'bg-secondary-subtle');
                        @endphp

                        <span class="badge {{ $class }} mb-2 fw-bold px-3 py-2" style="font-size: 0.7rem;">
                            {{ $label }}
                        </span>

                        <div class="d-block">
                            <span class="fw-800 text-dark" style="font-size: 2.8rem; letter-spacing: -2px; line-height: 1;">
                                $ {{ number_format($order->amount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <div class="order-lg-2 d-flex flex-column gap-2">
                        @if($isSigned)
                            <a href="{{ route('prescriptions.download', $activePrescription->id) }}" class="btn btn-success d-flex align-items-center justify-content-center gap-2 py-2 px-4 shadow-sm fw-bold">
                                <i class="bi bi-file-earmark-pdf-fill fs-5"></i> Descargar Orden
                            </a>
                        @elseif($order->status === 'pending')
                            <a href="{{ route('checkout.process', $order->id) }}" class="btn btn-primary px-4 py-2 shadow-sm fw-bold">
                                Pagar Ahora
                            </a>
                        @endif

                        @if($order->status !== 'pending')
                            <button class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2 py-2 px-4 position-relative"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#chat-{{ $order->id }}"
                                    wire:click="markAsRead">
                                <i class="bi bi-chat-left-text"></i>
                                <span>
                                    {{ $order->interactions->count() > 0 ? 'Ver Mensajes' : 'Consultar Médico' }}
                                </span>

                                @if($showNotificationBadge)
                                    <span class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle shadow"></span>
                                @endif
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($order->status !== 'pending')
            <div class="collapse {{ $showNotificationBadge ? 'show' : '' }}" id="chat-{{ $order->id }}" wire:ignore>
                <div class="chat-wrapper-custom mt-4 pt-4 border-top">
                    @livewire('patient.order-chat', ['order' => $order], key('chat-'.$order->id))
                </div>
            </div>
        @endif
    </div>
</div>
