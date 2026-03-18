@extends('layouts.front')

@section('title', 'Orden Personalizada - ' . config('app.name'))

@push('styles')
<style>
    /* Definición del degradado que faltaba */
    .bg-gradient-blue {
        background: linear-gradient(135deg, #0d6efd 0%, #0099ff 100%) !important;
    }

    /* Estilo para la tarjeta principal */
    .card-custom {
        background: #ffffff;
        border: none;
        border-radius: 30px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.07);
        overflow: hidden; /* Importante para que el banner respete el radio de las esquinas */
        margin-bottom: 2rem;
    }

    /* Ajustes de tipografía y suavizado */
    .ls-1 { letter-spacing: 0.5px; }

    .transition-all {
        transition: all 0.3s ease;
    }

    /* Animación sutil de entrada */
    .fade-in-up {
        animation: fadeInUp 0.5s ease-out;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        {{-- Ampliado a col-lg-10 para aprovechar mejor el espacio lateral --}}
        <div class="col-12 col-lg-10 fade-in-up">

            {{-- Breadcrumb / Volver --}}
            <div class="mb-4">
                <a href="{{ route('home') }}" class="text-decoration-none small fw-bold text-primary d-inline-flex align-items-center">
                    <i class="bi bi-arrow-left-circle-fill fs-5 me-2"></i> Volver al catálogo de exámenes
                </a>
            </div>

            <div class="card-custom">
                {{-- Banner de Identidad Visual (Corregido con fondo real) --}}
                <div class="bg-gradient-blue p-4 p-md-5 text-white text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-white bg-opacity-20 rounded-circle mb-3 shadow-sm" style="width: 60px; height: 60px;">
                        <i class="bi bi-magic fs-3 text-white"></i>
                    </div>
                    <h3 class="fw-bold mb-2 text-white">Solicitud de Orden Médica</h3>
                    <p class="mb-0 opacity-90 mx-auto text-white" style="max-width: 500px;">
                        Describe tus necesidades y un profesional de la salud validará la pertinencia clínica de tu orden.
                    </p>
                </div>

                <div class="card-body p-4 p-md-5">
                    {{-- COMPONENTE LIVEWIRE --}}
                    {{-- El componente ahora tendrá espacio para mostrarse en formato ancho --}}
                    @livewire('custom-order-flow', ['patient' => $patient])
                </div>
            </div>

            {{-- Footer de Información y Seguridad --}}
            <div class="row justify-content-center mt-5">
                <div class="col-md-8 text-center">
                    <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
                        <span class="badge bg-white text-dark border shadow-sm rounded-pill px-3 py-2 small">
                            <i class="bi bi-shield-check text-success me-1"></i> Protocolo HIPAA Compliant
                        </span>
                        <span class="badge bg-white text-dark border shadow-sm rounded-pill px-3 py-2 small">
                            <i class="bi bi-lock-fill text-primary me-1"></i> Encriptación SSL
                        </span>
                    </div>
                    <p class="small text-muted px-lg-5">
                        Al solicitar una orden médica, confirmas que la información proporcionada es verídica.
                        El documento emitido es una orden médica electrónica válida para su uso en centros de salud.
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
