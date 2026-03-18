@extends('layouts.front')

@section('title', 'Confirmar Orden - ' . config('app.name'))

@section('content')
<div class="container py-5" style="margin-top: 100px;">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-10">

            {{-- Encabezado de confianza (Manteniendo tu estilo visual) --}}
            <div class="text-center mb-4">
                <div class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill mb-3 fw-bold">
                    <i class="bi bi-lock-fill me-1"></i> Checkout Seguro
                </div>
                <h2 class="fw-bold h3 text-dark">Confirmar tu Orden</h2>
                <p class="text-muted">Has seleccionado <strong>{{ $selectedExams->count() }}</strong> exámenes</p>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">

                    {{-- LLAMADA AL COMPONENTE LIVEWIRE REUTILIZABLE --}}
                    {{-- Le pasamos los IDs como string para el modo múltiple --}}
                    @livewire('order-checkout', [
                        'selectedExamsIds' => $selectedExams->pluck('id')->implode(',')
                    ])

                </div>
            </div>

            {{-- Footer de Seguridad --}}
            <div class="text-center mt-5">
                <p class="text-muted small mb-4">
                    <i class="bi bi-shield-lock me-1 text-primary"></i>
                    Tus datos y transacciones están protegidos bajo estándares de seguridad médica.
                </p>

                <div class="d-flex justify-content-center align-items-center gap-4 filter-grayscale opacity-50">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/a/a4/Visa_2021.svg" height="15" alt="Visa">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" height="25" alt="Mastercard">
                    <img src="https://www.webpay.cl/portal/img/logo-webpay.png" height="20" alt="Webpay" style="filter: brightness(0);">
                </div>

                <div class="mt-4">
                    <a href="{{ route('home') }}" class="text-muted small text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i> Volver y modificar selección
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
