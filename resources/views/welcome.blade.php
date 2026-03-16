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
                        <i class="bi bi-search me-2"></i>Buscar Exámenes
                    </a>
                    <a href="#como-funciona" class="btn btn-outline-dark btn-lg rounded-pill px-5 py-3 fw-bold">
                        ¿Cómo funciona?
                    </a>
                </div>

                {{-- Mini Social Proof --}}
                <div class="mt-5 d-flex align-items-center gap-3">
                    <div class="d-flex">
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                    </div>
                    <span class="small text-muted fw-bold">+1.000 órdenes emitidas este mes</span>
                </div>
            </div>

            {{-- Imagen / Ilustración --}}
            <div class="col-lg-6 d-none d-lg-block">
                <div class="position-relative">
                    <img src="{{ asset('assets/img/hero-doctor.jpg') }}" alt="Médico revisando exámenes" class="img-fluid rounded-4 shadow-sm">
                    {{-- Elemento flotante para dar dinamismo --}}
                    <div class="position-absolute bottom-0 start-0 bg-white p-3 shadow-lg rounded-4 mb-4 ms-n4 border-start border-primary border-4 animate__animated animate__fadeInUp">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success bg-opacity-10 p-2 rounded-circle">
                                <i class="bi bi-check2-circle text-success fs-4"></i>
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
    <div class="container">
        <div class="row text-center g-4 justify-content-center">

            {{-- 1. FIRMA ELECTRÓNICA --}}
            <div class="col-6 col-md-4 col-lg-2">
                <div class="px-2">
                    <i class="bi bi-shield-check text-success fs-3 mb-2 d-block"></i>
                    <span class="d-block fw-bold text-dark small">Firma Avanzada</span>
                    <p class="text-muted mb-0" style="font-size: 0.72rem; line-height: 1.2;">
                        Validez legal según <br><strong>Ley 19.799</strong>.
                    </p>
                </div>
            </div>

            {{-- 2. DISPONIBILIDAD 24/7 --}}
            <div class="col-6 col-md-4 col-lg-2">
                <div class="px-2">
                    <i class="bi bi-clock-fill text-danger fs-3 mb-2 d-block"></i>
                    <span class="d-block fw-bold text-dark small">Disponibilidad 24/7</span>
                    <p class="text-muted mb-0" style="font-size: 0.72rem; line-height: 1.2;">
                        Solicita tu orden los <br>365 días del año.
                    </p>
                </div>
            </div>

            {{-- 3. ENTREGA EN MINUTOS --}}
            <div class="col-6 col-md-4 col-lg-2">
                <div class="px-2">
                    <i class="bi bi-lightning-charge-fill text-warning fs-3 mb-2 d-block"></i>
                    <span class="d-block fw-bold text-dark small">Entrega Veloz</span>
                    <p class="text-muted mb-0" style="font-size: 0.72rem; line-height: 1.2;">
                        Procesamiento y envío <br>en minutos.
                    </p>
                </div>
            </div>

            {{-- 4. LABORATORIOS --}}
            <div class="col-6 col-md-4 col-lg-2">
                <div class="px-2">
                    <i class="bi bi-hospital text-info fs-3 mb-2 d-block"></i>
                    <span class="d-block fw-bold text-dark small">Red Nacional</span>
                    <p class="text-muted mb-0" style="font-size: 0.72rem; line-height: 1.2;">
                        Válida en Fonasa, Isapre <br>y particulares.
                    </p>
                </div>
            </div>

            {{-- 5. PRIVACIDAD Y DATOS (LEY 21.719) --}}
            <div class="col-6 col-md-4 col-lg-2">
                <div class="px-2">
                    <i class="bi bi-lock-fill text-primary fs-3 mb-2 d-block"></i>
                    <span class="d-block fw-bold text-dark small">Datos Protegidos</span>
                    <p class="text-muted mb-0" style="font-size: 0.72rem; line-height: 1.2;">
                        Resguardados bajo la <br><strong>Ley 21.719</strong>.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

    {{-- PASOS --}}
    {{-- PASOS --}}
    <section id="como-funciona" class="py-5">
        <div class="container py-4 text-center">
            <h2 class="fw-bold mb-5">Obtén tu orden médica en 3 pasos</h2>
            <div class="row g-4">
                {{-- PASO 1 --}}
                <div class="col-md-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-list-check fs-3"></i>
                    </div>
                    <h5 class="fw-bold">1. Elige tu Examen</h5>
                    <p class="text-muted small px-lg-4">Selecciona un pack preventivo o solicita una orden personalizada según lo que necesites.</p>
                </div>

                {{-- PASO 2: EL CAMBIO --}}
                <div class="col-md-4">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-credit-card fs-3"></i>
                    </div>
                    <h5 class="fw-bold">2. Paga Online</h5>
                    <p class="text-muted small px-lg-4">Evita filas, tiempos de desplazamiento y exponerte a virus en salas de espera. Proceso 100% digital.</p>
                </div>

                {{-- PASO 3 --}}
                <div class="col-md-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-file-earmark-pdf fs-3"></i>
                    </div>
                    <h5 class="fw-bold">3. Recibe tu Orden</h5>
                    <p class="text-muted small px-lg-4">Descarga tu PDF firmado por médicos colegiados y preséntalo directamente en cualquier laboratorio.</p>
                </div>
            </div>
        </div>
    </section>


    {{-- PRODUCTOS --}}


    {{-- PRODUCTOS --}}
    <section id="packs" class="py-5 bg-light rounded-5 mx-2 shadow-inner">
        <div class="container">
            {{-- 1. PACKS PREVENTIVOS --}}
            <h3 class="fw-bold mb-4"><i class="bi bi-collection-fill text-primary"></i> Packs Preventivos</h3>
            <div class="row g-4 mb-5">
                @foreach($packs as $pack)
                <div class="col-lg-4 col-md-6">
                    <div class="card card-pack p-4 shadow-sm border-0 h-100 rounded-4">
                        <div class="mb-3">
                            <span class="badge {{ $loop->first ? 'bg-primary' : 'bg-secondary bg-opacity-10 text-secondary' }} rounded-pill px-3 py-2">
                                {{ $loop->first ? 'MÁS SOLICITADO' : 'PACK FAMILIAR' }}
                            </span>
                        </div>
                        <h4 class="fw-bold mb-3">{{ $pack->name }}</h4>
                        <div class="flex-grow-1 mb-4">
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($pack->children as $child)
                                    <div class="exam-chip bg-white border small px-2 py-1 rounded-3">
                                        <i class="bi bi-check-circle-fill text-success"></i> {{ $child->name }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="pt-4 border-top">
                            <span class="price-text fs-3 fw-bold">${{ number_format($pack->base_price, 0, ',', '.') }}</span>
                            <a href="{{ route('order.flow', ['type' => 'pack', 'id' => $pack->id]) }}" class="btn btn-primary w-100 mt-3 py-3 fw-bold shadow-sm">Seleccionar Pack</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- 2. BANNER ORDEN PERSONALIZADA (Alternativa si no encuentran pack) --}}
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
                {{-- Decoración sutil de fondo --}}
                <i class="bi bi-clipboard2-pulse position-absolute text-white opacity-10" style="font-size: 10rem; right: -20px; bottom: -30px; transform: rotate(-15deg);"></i>
            </div>

            {{-- 3. EXÁMENES INDIVIDUALES --}}
            <div id="frecuentes">
                <h3 class="fw-bold mb-4"><i class="bi bi-search text-primary"></i> Exámenes Frecuentes</h3>
                <div class="row g-3">
                    @foreach($individuales as $exam)
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="card h-100 shadow-sm border-0 p-3 rounded-4">
                            <div class="d-flex flex-column h-100">
                                <h6 class="fw-bold mb-2 text-dark">{{ $exam->name }}</h6>
                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <span class="text-primary fw-bold">${{ number_format($exam->base_price, 0, ',', '.') }}</span>
                                    <a href="{{ route('order.flow', ['type' => 'exam', 'id' => $exam->id]) }}" class="btn btn-outline-primary btn-sm px-3 rounded-pill">Pedir</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>


@endsection
