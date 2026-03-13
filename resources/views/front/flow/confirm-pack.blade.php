@extends('layouts.front')

@section('title', 'Finalizar Compra - MedOrder Flow')

@push('styles')
<style>

</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            {{-- Encabezado de confianza --}}
            <div class="text-center mb-4">
                <div class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill mb-3 fw-bold">
                    <i class="bi bi-lock-fill me-1"></i> Checkout Seguro
                </div>
                <h2 class="fw-bold h3 text-dark">Finalizar Compra</h2>
                <p class="text-muted">Estás solicitando: <strong>{{ $exam_type->name }}</strong></p>
            </div>

            <div class="card-confirm">
                <div class="card-body p-4 p-md-5">
                    {{-- LLAMADA AL COMPONENTE LIVEWIRE --}}
                    {{-- Este componente manejará la selección de paciente y el botón de pago --}}
                    @livewire('order-checkout', ['examTypeId' => $exam_type->id])
                </div>
            </div>

            {{-- Footer de Seguridad y Pagos --}}
            <div class="text-center mt-5">
                <p class="text-muted small mb-4">
                    <i class="bi bi-shield-lock me-1 text-primary"></i>
                    Tus datos y transacciones están protegidos bajo estándares de seguridad médica.
                </p>

                <div class="d-flex justify-content-center align-items-center gap-4 filter-grayscale">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/a/a4/Visa_2021.svg" height="15" alt="Visa">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" height="25" alt="Mastercard">
                    <img src="https://www.webpay.cl/portal/img/logo-webpay.png" height="20" alt="Webpay" style="filter: brightness(0);">
                </div>

                <div class="mt-4">
                    <a href="{{ route('home') }}" class="text-muted small text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i> Volver y seguir buscando
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
