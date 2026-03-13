@extends('layouts.front')

@section('title', 'Finalizar Compra - MedOrder Flow')

@push('styles')
<style>

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
                    @livewire('order-checkout', ['examTypeId' => $exam_type->id])

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
