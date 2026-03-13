@extends('layouts.front')

@section('title', 'Finalizar Compra - MedOrder Flow')

@push('styles')
<style>
    body {
        background-color: #f8faff;
        background-image: radial-gradient(#d1d9e6 0.5px, transparent 0.5px);
        background-size: 20px 20px;
    }

    .card-confirm {
        border: none;
        border-radius: 28px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.06);
        overflow: hidden;
        background: white;
    }

    /* Stepper simple para indicar progreso */
    .step-indicator {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .step-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #dee2e6;
    }
    .step-dot.active {
        background-color: var(--bs-primary);
        transform: scale(1.3);
    }

    .form-control, .form-select {
        border: 1px solid #e2e8f0;
        padding: 12px 16px;
        border-radius: 12px;
        transition: all 0.2s;
    }
    .form-control:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
    }

    .price-box {
        background-color: #f1f5f9;
        border-radius: 15px;
        padding: 15px;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-6">

            {{-- Indicador de Pasos --}}
            <div class="step-indicator">
                <div class="step-dot"></div>
                <div class="step-dot"></div>
                <div class="step-dot active"></div>
            </div>

            <div class="text-center mb-4">
                <h2 class="fw-bold h3 text-dark">Confirmar Solicitud</h2>
                <p class="text-muted">Estás solicitando: <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">{{ $exam_type->name }}</span></p>
            </div>

            <div class="card-confirm shadow-lg">
                <div class="card-body p-4 p-md-5">

                    {{-- Mini Resumen de Compra --}}
                    <div class="price-box d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <span class="text-muted small d-block">Monto a pagar</span>
                            <span class="fw-bold h4 mb-0 text-primary">${{ number_format($exam_type->base_price, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-end">
                            <i class="bi bi-cart-check fs-2 text-primary opacity-25"></i>
                        </div>
                    </div>

                    {{-- Componente Livewire --}}
                    @livewire('order-checkout-form', ['examTypeId' => $exam_type->id])

                </div>
            </div>

            {{-- Footer de Confianza --}}
            <div class="text-center mt-5">
                <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/b/ba/SSL_logo.png" alt="SSL" height="20" class="opacity-50">
                    <p class="text-muted small mb-0">
                        <i class="bi bi-shield-lock-fill me-1 text-success"></i>
                        Transacción protegida y cifrada (Ley 20.584)
                    </p>
                </div>

                <div class="d-flex justify-content-center gap-4 opacity-50 filter-grayscale">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" height="12" alt="Visa">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" height="20" alt="Mastercard">
                    <img src="https://www.webpay.cl/portal/img/logo-webpay.png" height="15" alt="Webpay" style="filter: brightness(0);">
                </div>

                <div class="mt-4">
                    <a href="{{ route('home') }}" class="text-muted small text-decoration-none hover-primary">
                        <i class="bi bi-arrow-left me-1"></i> Cancelar y volver al inicio
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
