@extends('layouts.front')

@section('title', $config['title'] . ' - ' . config('app.name'))

@push('styles')
<style>
    .card-status {
        border-radius: 28px;
        border: none;
        box-shadow: 0 15px 35px rgba(0,0,0,0.05);
    }
    /* Colores dinámicos para el icono */
    .status-icon {
        width: 80px;
        height: 80px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-bottom: 1.5rem;
    }
    .icon-success { background-color: #d1e7dd; color: #0f5132; }
    .icon-error { background-color: #f8d7da; color: #842029; }
    .icon-warning { background-color: #fff3cd; color: #664d03; }

    .detail-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <div class="card card-status p-4 p-md-5">
                <div class="card-body text-center">

                    {{-- Icono Dinámico --}}
                    <div class="status-icon {{ 'icon-' . ($config['status'] ?? 'warning') }}">
                        @if($config['status'] === 'success')
                            <i class="bi bi-check-lg fs-1"></i>
                        @elseif($config['status'] === 'error')
                            <i class="bi bi-x-lg fs-1"></i>
                        @else
                            <i class="bi bi-clock-history fs-1"></i>
                        @endif
                    </div>

                    <h2 class="fw-800 mb-2">{{ $config['title'] }}</h2>
                    <p class="text-muted mb-4">
                        {!! $config['message'] !!}
                    </p>

                    @if($gatewayTrx)
                    <div class="detail-box p-4 text-start mb-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-uppercase small text-secondary">
                            Referencia de Pago
                        </h6>
                        <dl class="row mb-0 small">
                            <dt class="col-6 text-muted fw-normal">Orden de Compra:</dt>
                            <dd class="col-6 text-end fw-bold">{{ $gatewayTrx->buy_order }}</dd>

                            <dt class="col-6 text-muted fw-normal">Estado Pago:</dt>
                            <dd class="col-6 text-end">
                                <span class="badge {{ $config['status'] === 'success' ? 'bg-success' : ($config['status'] === 'error' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                    {{ strtoupper($config['status']) }}
                                </span>
                            </dd>

                            <dt class="col-6 text-muted fw-normal">Monto:</dt>
                            <dd class="col-6 text-end fw-bold">$ {{ number_format($gatewayTrx->amount, 0, ',', '.') }}</dd>
                        </dl>
                    </div>
                    @endif

                    {{-- Acciones dinámicas --}}
                    <div class="d-grid gap-2">
                        @if($config['status'] === 'success')
                            <a href="{{ route('patient.orders') }}" class="btn btn-primary btn-lg fw-bold shadow-sm">
                                <i class="bi bi-file-earmark-medical me-2"></i> Ver mi Orden Médica
                            </a>
                        @elseif($config['status'] === 'error')
                            <a href="{{ route('order.flow', ['type' => 'standard']) }}" class="btn btn-danger btn-lg fw-bold shadow-sm">
                                <i class="bi bi-arrow-clockwise me-2"></i> Intentar Nuevamente
                            </a>
                        @else
                            <a href="{{ route('patient.orders') }}" class="btn btn-warning btn-lg fw-bold shadow-sm">
                                <i class="bi bi-search me-2"></i> Revisar mi historial
                            </a>
                        @endif

                        <a href="{{ route('home') }}" class="btn btn-link text-muted fw-bold">
                            Volver al Inicio
                        </a>
                    </div>

                </div>
            </div>

            <p class="text-center mt-4 text-muted small">
                Si tienes dudas sobre tu pago, escríbenos a <br>
                <strong>contacto@doctor911.cl</strong> mencionando tu orden de compra.
            </p>

        </div>
    </div>
</div>
@endsection
