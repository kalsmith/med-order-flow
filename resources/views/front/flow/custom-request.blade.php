@extends('layouts.front')

@section('title', 'Orden Personalizada - MedOrder Flow')

@push('styles')
<style>

</style>
@endpush

@section('content')
<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            {{-- Breadcrumb / Volver --}}
            <div class="mb-4">
                <a href="{{ route('home') }}" class="text-decoration-none small fw-bold text-primary">
                    <i class="bi bi-arrow-left me-1"></i> Volver al catálogo de exámenes
                </a>
            </div>

            <div class="card-custom">
                {{-- Banner de Identidad Visual --}}
                <div class="bg-gradient-blue p-4 text-white text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-white bg-opacity-20 rounded-circle mb-3" style="width: 50px; height: 50px;">
                        <i class="bi bi-magic fs-4"></i>
                    </div>
                    <h4 class="fw-bold mb-1 text-white">Solicitud de Orden a Medida</h4>
                    <p class="mb-0 opacity-75 small text-white">Describe tus necesidades y un médico validará la orden</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    {{-- COMPONENTE LIVEWIRE --}}
                    @livewire('custom-order-flow', ['patient' => $patient])
                </div>
            </div>

            {{-- Info de Seguridad --}}
            <div class="text-center mt-4">
                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">
                        <i class="bi bi-shield-check me-1"></i> Protocolo HIPAA Compliant
                    </span>
                </div>
                <p class="small text-muted">
                    Al solicitar una orden, aceptas que un profesional médico evalúe tu caso para asegurar la pertinencia clínica del examen.
                </p>
            </div>

        </div>
    </div>
</div>
@endsection
