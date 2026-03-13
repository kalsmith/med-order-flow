@extends('layouts.front')

@section('title', 'Mis Órdenes - MedOrder Flow')

@push('styles')
<style>
    .card-order {
        border-radius: 20px;
        border: none;
        transition: all 0.25s ease;
        background: #ffffff;
    }
    .card-order:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.06) !important;
    }
    .badge-status {
        border-radius: 10px;
        padding: 0.6em 1.2em;
        font-weight: 700;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .patient-tag {
        font-size: 0.75rem;
        background: #f0f7ff;
        color: #0056b3;
        padding: 4px 12px;
        border-radius: 8px;
        font-weight: 600;
    }
    .info-box {
        border-radius: 16px;
        border-left: 4px solid !important;
    }
    .bg-waiting {
        background-color: #f0f7ff;
        border-color: #0d6efd !important;
    }
    .empty-state {
        padding: 5rem 2rem;
        background: white;
        border-radius: 24px;
    }
    .swal2-popup-custom { border-radius: 24px !important; font-family: 'Inter', sans-serif; }
</style>
@endpush

@section('content')
<div class="container py-5">
    {{-- Header de la Sección --}}
    <div class="row mb-5 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-800 mb-1" style="letter-spacing: -1.5px;">Mis Órdenes Médicas</h2>
            <p class="text-muted mb-0">Gestiona tus solicitudes y descarga tus documentos.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('home') }}" class="btn btn-primary rounded-4 fw-bold shadow-sm px-4 py-2">
                <i class="bi bi-plus-lg me-2"></i> Nueva Solicitud
            </a>
        </div>
    </div>

    @if($orders->isEmpty())
        <div class="empty-state text-center shadow-sm">
            <div class="mb-4">
                <i class="bi bi-file-earmark-medical display-1 text-primary opacity-25"></i>
            </div>
            <h4 class="fw-bold">Aún no tienes órdenes</h4>
            <p class="text-muted mx-auto" style="max-width: 400px;">
                Tus órdenes aparecerán aquí una vez que realices una solicitud. Podrás descargarlas en formato PDF.
            </p>
            <a href="{{ route('home') }}" class="btn btn-outline-primary rounded-4 fw-bold mt-3">Comenzar ahora</a>
        </div>
    @else
        <div class="row g-4">
            @foreach($orders as $order)
            <div class="col-12">
                <div class="card card-order shadow-sm">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            {{-- Info Principal --}}
                            <div class="col-lg-5 mb-3 mb-lg-0">
                                <div class="d-flex align-items-center mb-3 flex-wrap gap-2">
                                    <span class="badge bg-white text-dark border fw-bold" style="font-size: 0.7rem; border-radius: 6px;">
                                        ID: {{ strtoupper(substr($order->id, 0, 8)) }}
                                    </span>
                                    <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i> {{ $order->created_at->format('d/m/Y H:i') }}</span>
                                    <span class="patient-tag"><i class="bi bi-person-fill me-1"></i>{{ $order->patient->full_name ?? 'Titular' }}</span>
                                </div>

                                <h5 class="fw-bold mb-0 text-dark">
                                    {{ $order->type === 'custom' ? 'Solicitud Especial' : ($order->examType->name ?? 'Examen General') }}
                                </h5>

                                {{-- Estado: En Revisión --}}
                                @if($order->status === 'paid')
                                    <div class="mt-3 p-3 info-box bg-waiting shadow-sm">
                                        <div class="d-flex align-items-center">
                                            <div class="spinner-border spinner-border-sm text-primary me-3" role="status"></div>
                                            <div>
                                                <span class="d-block fw-bold text-dark mb-1" style="font-size: 0.85rem;">Médico revisando su solicitud</span>
                                                <span class="text-muted d-block small" style="line-height: 1.4;">
                                                    Plazo aproximado: <strong>24 horas</strong>. Te avisaremos por email.
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Estado: Reembolso --}}
                                @if($order->status === 'refund_pending')
                                    <div class="mt-3 p-3 info-box bg-light border-info shadow-sm">
                                        <div class="d-flex">
                                            <i class="bi bi-info-circle-fill text-info me-2 fs-5"></i>
                                            <div>
                                                <span class="d-block fw-bold text-dark small">Reembolso en proceso</span>
                                                <span class="text-muted d-block small">Revisa el correo de Flow para completar tus datos bancarios.</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Monto y Badge --}}
                            <div class="col-lg-3 text-lg-center mb-3 mb-lg-0">
                                <div class="mb-2">
                                    @php
                                        $statusConfig = [
                                            'pending' => ['class' => 'bg-warning-subtle text-warning border-warning-subtle', 'label' => 'Pendiente de Pago'],
                                            'paid' => ['class' => 'bg-info-subtle text-info border-info-subtle', 'label' => 'En Revisión'],
                                            'signed' => ['class' => 'bg-success-subtle text-success border-success-subtle', 'label' => 'Lista para Descarga'],
                                            'refund_pending' => ['class' => 'bg-primary-subtle text-primary border-primary-subtle', 'label' => 'Reembolso Enviado'],
                                            'rejected' => ['class' => 'bg-danger-subtle text-danger border-danger-subtle', 'label' => 'Rechazada'],
                                        ];
                                        $curr = $statusConfig[$order->status] ?? ['class' => 'bg-secondary-subtle', 'label' => $order->status];
                                    @endphp
                                    <span class="badge badge-status border {{ $curr['class'] }}">{{ $curr['label'] }}</span>
                                </div>
                                <div class="fw-800 text-dark fs-4">$ {{ number_format($order->amount, 0, ',', '.') }}</div>
                            </div>

                            {{-- Botones de Acción --}}
                            <div class="col-lg-4 text-lg-end">
                                <div class="d-flex gap-2 justify-content-lg-end">
                                    @if($order->status === 'pending')
                                        <a href="{{ route('checkout.process', $order->id) }}" class="btn btn-primary rounded-4 fw-bold flex-grow-1 flex-lg-grow-0 px-4">
                                            <i class="bi bi-credit-card me-2"></i> Pagar
                                        </a>
                                    @elseif($order->status === 'signed')
                                        <a href="{{ route('orders.download', $order->id) }}" class="btn btn-success text-white rounded-4 fw-bold flex-grow-1 flex-lg-grow-0 px-4">
                                            <i class="bi bi-file-earmark-arrow-down-fill me-2"></i> Descargar PDF
                                        </a>
                                    @endif

                                    @if($order->rejection_reason)
                                        <button type="button"
                                                class="btn btn-outline-secondary rounded-4 fw-bold px-3 btn-show-reason"
                                                data-reason="{{ $order->rejection_reason }}"
                                                data-id="{{ strtoupper(substr($order->id, 0, 8)) }}">
                                            <i class="bi bi-chat-left-text me-1"></i> Motivo
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-5 d-flex justify-content-center">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reasonButtons = document.querySelectorAll('.btn-show-reason');
        reasonButtons.forEach(button => {
            button.addEventListener('click', function() {
                Swal.fire({
                    title: 'Nota del Médico',
                    html: `<div class="text-start p-2">
                            <p class="text-muted mb-2 small text-uppercase fw-bold">Referencia: Orden #${this.dataset.id}</p>
                            <p class="mb-0">${this.dataset.reason}</p>
                           </div>`,
                    icon: 'info',
                    confirmButtonColor: '#0d6efd',
                    confirmButtonText: 'Entendido',
                    customClass: { popup: 'swal2-popup-custom' }
                });
            });
        });
    });
</script>
@endpush
