@extends('layouts.front')

@section('title', config('app.name') . ' - Órdenes Médicas al Instante')

@section('meta')
    {{-- Open Graph / Facebook / WhatsApp --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="PideTuExamen - Tu orden médica lista en minutos">
    <meta property="og:description" content="Obtén tu orden oficial firmada por médicos colegiados, válida para Fonasa, Isapre y todos los laboratorios de Chile.">
    <meta property="og:image" content="{{ asset('assets/img/og-main.jpg') }}">

    {{-- Twitter / X --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url('/') }}">
    <meta name="twitter:title" content="PideTuExamen - Tu orden médica lista en minutos">
    <meta name="twitter:description" content="Obtén tu orden oficial firmada por médicos en Chile. 100% online.">
    <meta name="twitter:image" content="{{ asset('assets/img/og-main.jpg') }}">

    {{-- SEO Standard --}}
    <meta name="description" content="Obtén tu orden oficial firmada por médicos colegiados, válida para Fonasa, Isapre y todos los laboratorios de Chile.">
@endsection

@section('content')
{{-- HERO SECTION --}}
<header class="hero-section py-5 bg-light position-relative overflow-hidden" style="background: linear-gradient(135deg, #f8faff 0%, #eef4ff 100%);">
    <div class="container py-lg-5">
        <div class="row align-items-center">
            <div class="col-lg-6 text-start">
                <span class="badge bg-primary bg-opacity-10 text-primary py-2 px-3 rounded-pill mb-4 border border-primary border-opacity-25 fw-bold">
                    ✨ 100% Online · Firma Avanzada · Chile
                </span>
                <h1 class="display-3 hero-title text-dark mb-4 fw-bold" style="line-height: 1.1;">
                    Tu orden médica <br>
                    <span class="text-primary">lista en minutos.</span>
                </h1>
                <p class="lead text-muted mb-5" style="font-size: 1.2rem;">
                    Evita esperas innecesarias. Obtén tu orden oficial firmada por médicos colegiados, válida para Fonasa, Isapre y todos los laboratorios de Chile.
                </p>
                <div class="d-flex flex-column flex-md-row gap-3">
                    <a href="#packs" class="btn btn-primary btn-lg rounded-pill px-5 py-3 shadow-lg fw-bold">
                        <i class="bi bi-cart-plus me-2"></i>Ver Exámenes
                    </a>
                    <a href="#como-funciona" class="btn btn-outline-dark btn-lg rounded-pill px-5 py-3 fw-bold">
                        ¿Cómo funciona?
                    </a>
                </div>

                <div class="mt-5 d-flex align-items-center gap-3">
                    <div class="d-flex text-warning">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                    </div>
                    <span class="small text-muted fw-bold">+1.000 órdenes emitidas este mes</span>
                </div>
            </div>

            <div class="col-lg-6 d-none d-lg-block">
                <div class="position-relative">
                    <img src="{{ asset('assets/img/hero-doctor.jpg') }}" alt="Médico" class="img-fluid rounded-4 shadow-sm">
                    <div class="position-absolute bottom-0 start-0 bg-white p-3 shadow-lg rounded-4 mb-4 ms-n4 border-start border-primary border-4 animate__animated animate__fadeInUp">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success bg-opacity-10 p-2 rounded-circle text-success fs-4">
                                <i class="bi bi-check2-circle"></i>
                            </div>
                            <div>
                                <p class="mb-0 fw-bold small">Firma Verificada</p>
                                <p class="mb-0 text-muted small">Ley 20.584 cumplida</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>


{{-- TRUST BADGES --}}
<section class="py-5 border-top border-bottom bg-white">
    <div class="container text-center">
        <div class="row g-4 justify-content-center">
            {{-- <div class="col-6 col-md-4 col-lg-2">
                <i class="bi bi-shield-check text-success fs-3 d-block mb-2"></i>
                <span class="fw-bold small d-block">Firma Avanzada</span>
                <p class="text-muted small mb-0">Ley 19.799</p>
            </div> --}}
            <div class="col-6 col-md-4 col-lg-3">
                <i class="bi bi-clock-fill text-danger fs-3 d-block mb-2"></i>
                <span class="fw-bold small d-block">Disponibilidad 24/7</span>
                <p class="text-muted small mb-0">365 días del año</p>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <i class="bi bi-lightning-charge-fill text-warning fs-3 d-block mb-2"></i>
                <span class="fw-bold small d-block">Entrega Veloz</span>
                <p class="text-muted small mb-0">En minutos</p>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <i class="bi bi-hospital text-info fs-3 d-block mb-2"></i>
                <span class="fw-bold small d-block">Red Nacional</span>
                <p class="text-muted small mb-0">Fonasa e Isapre</p>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <i class="bi bi-lock-fill text-primary fs-3 d-block mb-2"></i>
                <span class="fw-bold small d-block">Datos Protegidos</span>
                <p class="text-muted small mb-0">Ley 21.719</p>
            </div>
        </div>
    </div>
</section>

{{-- PASOS --}}
<section id="como-funciona" class="py-5">
    <div class="container py-4 text-center">
        <h2 class="fw-bold mb-5">Obtén tu orden médica en 3 pasos</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-list-check fs-3"></i>
                </div>
                <h5 class="fw-bold">1. Elige tu Examen</h5>
                <p class="text-muted small px-lg-4">Selecciona un pack preventivo o solicita una orden personalizada.</p>
            </div>
            <div class="col-md-4">
                <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-credit-card fs-3"></i>
                </div>
                <h5 class="fw-bold">2. Paga Online</h5>
                <p class="text-muted small px-lg-4">Proceso 100% digital. Olvida las filas y las salas de espera.</p>
            </div>
            <div class="col-md-4">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-file-earmark-pdf fs-3"></i>
                </div>
                <h5 class="fw-bold">3. Recibe tu Orden</h5>
                <p class="text-muted small px-lg-4">Descarga tu PDF firmado y preséntalo en cualquier laboratorio.</p>
            </div>
        </div>
    </div>
</section>

{{-- SECCIÓN DE PRODUCTOS --}}
<section id="packs" class="py-5 bg-light rounded-5 mx-2 shadow-inner">
    <div class="container">
        {{-- 1. PACKS PREVENTIVOS --}}
        <h3 class="fw-bold mb-4"><i class="bi bi-collection-fill text-primary"></i> Packs Preventivos</h3>

@php $limit = 4; @endphp

<div class="row g-4 mb-5">
    @foreach($packs as $pack)
    <div class="col-lg-4 col-md-6">
        <div class="card card-pack p-4 shadow-sm border-0 h-100 rounded-4 d-flex flex-column transition-hover">

            {{-- Header: Badges y Link Informativo --}}
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <span class="badge {{ $loop->first ? 'bg-primary' : 'bg-secondary bg-opacity-10 text-secondary' }} rounded-pill px-3 py-2">
                    {{ $loop->first ? 'MÁS SOLICITADO' : 'PACK RECOMENDADO' }}
                </span>

                @if($pack->post_id && $pack->post)
                    <a href="{{ route('blog.show', $pack->post->slug) }}"
                       class="info-pill text-decoration-none shadow-sm"
                       title="Leer guía médica sobre este examen">
                        <span class="info-pill-icon">
                            <i class="bi bi-journal-medical"></i>
                        </span>
                        <span class="info-pill-text">Guía Médica</span>
                    </a>
                @endif
            </div>

            <h4 class="fw-bold mb-1 text-dark">{{ $pack->name }}</h4>

            @if($pack->description)
                <p class="text-muted small mb-3 italic">
                    {{ $pack->description }}
                </p>
            @endif

            {{-- Lista de Exámenes (Chips) --}}
            <div class="flex-grow-1 mb-4 text-start">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($pack->children->take($limit) as $child)
                        <div class="exam-chip bg-white border small px-2 py-1 rounded-3">
                            <i class="bi bi-check-circle-fill text-success"></i> {{ $child->name }}
                        </div>
                    @endforeach

                    @if($pack->children->count() > $limit)
                        @php
                            $remainingExams = $pack->children->slice($limit)->pluck('name')->implode(', ');
                        @endphp
                        <div class="exam-chip bg-light border small px-2 py-1 rounded-3 text-secondary fw-bold"
                             data-bs-toggle="tooltip"
                             data-bs-placement="top"
                             title="{{ $remainingExams }}"
                             style="cursor: help;">
                            + {{ $pack->children->count() - $limit }} más
                        </div>
                    @endif
                </div>
            </div>

            {{-- Footer: Precio y Call to Action --}}
            <div class="pt-4 border-top mt-auto">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="text-muted small d-block mb-0">Total Pack</span>
                        @if($pack->post_id && $pack->post)
                            <a href="{{ route('blog.show', $pack->post->slug) }}" class="info-link-sub">
                                <i class="bi bi-file-earmark-text me-1"></i>Ver detalles
                            </a>
                        @endif
                    </div>
                    <div class="text-end">
                        <span class="price-text fs-3 fw-bold text-primary">${{ number_format($pack->base_price, 0, ',', '.') }}</span>
                    </div>
                </div>

                <a href="{{ route('order.flow', ['type' => 'pack', 'id' => $pack->id]) }}"
                   class="btn btn-primary w-100 py-3 fw-bold shadow-sm rounded-3 btn-buy">
                    Seleccionar Pack <i class="bi bi-chevron-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>

<style>
    /* Efecto de Elevación de la Card */
    .transition-hover {
        transition: all 0.3s ease;
        border: 1px solid transparent !important;
    }
    .transition-hover:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.12) !important;
        border-color: rgba(13, 110, 253, 0.2) !important;
    }

    /* Estilo del Badge Informativo (Cápsula) */
    .info-pill {
        background: rgba(13, 110, 253, 0.06);
        border: 1px solid rgba(13, 110, 253, 0.15);
        border-radius: 50px;
        padding: 3px 10px 3px 4px;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }
    .info-pill-icon {
        background: #0d6efd;
        color: white;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-right: 6px;
        font-size: 0.7rem;
    }
    .info-pill-text {
        font-size: 0.65rem;
        font-weight: 800;
        color: #0d6efd;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .info-pill:hover {
        background: #0d6efd;
        transform: scale(1.05);
    }
    .info-pill:hover .info-pill-text { color: white; }
    .info-pill:hover .info-pill-icon { background: white; color: #0d6efd; }

    /* Link secundario debajo del precio */
    .info-link-sub {
        font-size: 0.72rem;
        color: #6c757d;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s;
    }
    .info-link-sub:hover { color: #0d6efd; text-decoration: underline; }

    /* Botón de compra animado */
    .btn-buy { transition: all 0.3s ease; }
    .btn-buy:hover { letter-spacing: 0.5px; filter: brightness(1.1); }
</style>


        {{-- 2. BANNER ORDEN PERSONALIZADA --}}
        <div class="card bg-dark text-white border-0 shadow-lg p-4 p-md-5 rounded-5 mb-5 overflow-hidden position-relative">
            <div class="row align-items-center position-relative" style="z-index: 2;">
                <div class="col-md-8 mb-4 mb-md-0">
                    <h2 class="fw-bold mb-3">¿No encuentras el examen que buscas?</h2>
                    <p class="lead opacity-75 mb-0">Carga tu lista de exámenes y un médico colegiado emitirá una orden personalizada a tu medida.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="mb-3">
                        <span class="fs-4">Desde</span>
                        <span class="display-6 fw-bold text-primary"> $9.990</span>
                    </div>
                    <a href="{{ route('order.flow', ['type' => 'personalizada']) }}" class="btn btn-primary btn-lg px-5 py-3 fw-bold rounded-pill shadow">Solicitar a Medida</a>
                </div>
            </div>
            <i class="bi bi-clipboard2-pulse position-absolute text-white opacity-10" style="font-size: 10rem; right: -20px; bottom: -30px; transform: rotate(-15deg);"></i>
        </div>

        {{-- 3. EXÁMENES INDIVIDUALES --}}
        <div id="frecuentes" class="mb-5">
            <h3 class="fw-bold mb-4"><i class="bi bi-star-fill text-warning"></i> Exámenes Frecuentes</h3>
            <div class="row g-3">
                @foreach($individuales as $exam)
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card h-100 shadow-sm border-0 p-3 rounded-4">
                        <div class="d-flex flex-column h-100">
                            <h6 class="fw-bold mb-2 text-dark">{{ $exam->name }}</h6>
                            <div class="mt-auto d-flex justify-content-between align-items-center pt-2">
                                <span class="text-primary fw-bold">${{ number_format($exam->base_price, 0, ',', '.') }}</span>
                                <a href="{{ route('order.flow', ['type' => 'exam', 'id' => $exam->id]) }}" class="btn btn-outline-primary btn-sm px-3 rounded-pill">Pedir</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- BUSCADOR LIVEWIRE (El rescate si no encontraron lo anterior) --}}
        <div class="bg-white p-4 p-md-5 rounded-5 shadow-sm border border-primary border-opacity-10">
            <div class="text-center mb-4">
                <h4 class="fw-bold">¿Necesitas otro examen?</h4>
                <p class="text-muted">Busca en nuestro catálogo completo de exámenes individuales.</p>
            </div>

            <div class="mx-auto" style="max-width: 700px;">
                @livewire('exam-search')
            </div>
        </div>

    </div>
</section>
@endsection
