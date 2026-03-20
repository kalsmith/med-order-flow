@extends('layouts.front')

@section('title', config('app.name') . ' - Órdenes Médicas al Instante')

@section('meta')
    {{-- SEO Standard & Canonical --}}
    <meta name="description" content="Obtén tu orden médica oficial firmada por médicos colegiados, válida para Fonasa, Isapre y todos los laboratorios de Chile. Entrega en minutos.">
    <link rel="canonical" href="{{ url('/') }}">

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

    {{-- JSON-LD: Microdatos para Google --}}
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "MedicalWebPage",
      "name": "PideTuExamen - Órdenes Médicas al Instante",
      "description": "Servicio de emisión de órdenes médicas online válidas en todo Chile.",
      "mainEntity": {
        "@type": "ItemList",
        "name": "Packs de Exámenes Preventivos",
        "itemListElement": [
          @foreach($packs as $index => $pack)
          {
            "@type": "ListItem",
            "position": {{ $index + 1 }},
            "item": {
              "@type": "Product",
              "name": "{{ $pack->name }}",
              "description": "{{ $pack->description ?? 'Pack de exámenes preventivos con firma médica.' }}",
              "offers": {
                "@type": "Offer",
                "price": "{{ $pack->base_price }}",
                "priceCurrency": "CLP",
                "availability": "https://schema.org/InStock"
              }
            }
          }{{ !$loop->last ? ',' : '' }}
          @endforeach
        ]
      }
    }
    </script>
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
                    <img src="{{ asset('assets/img/hero-doctor.jpg') }}" alt="Médico colegiado" class="img-fluid rounded-4 shadow-sm">
                    <div class="position-absolute bottom-0 start-0 bg-white p-3 shadow-lg rounded-4 mb-4 ms-n4 border-start border-primary border-4">
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
                <h3 class="h5 fw-bold">1. Elige tu Examen</h3>
                <p class="text-muted small px-lg-4">Selecciona un pack o solicita una orden personalizada.</p>
            </div>
            <div class="col-md-4">
                <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-credit-card fs-3"></i>
                </div>
                <h3 class="h5 fw-bold">2. Paga Online</h3>
                <p class="text-muted small px-lg-4">Proceso 100% digital.</p>
            </div>
            <div class="col-md-4">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-file-earmark-pdf fs-3"></i>
                </div>
                <h3 class="h5 fw-bold">3. Recibe tu Orden</h3>
                <p class="text-muted small px-lg-4">Descarga tu PDF firmado.</p>
            </div>
        </div>
    </div>
</section>

{{-- SECCIÓN DE PRODUCTOS --}}
<section id="packs" class="py-5 bg-light rounded-5 mx-2 shadow-inner">
    <div class="container">
        <h2 class="fw-bold mb-4 h3"><i class="bi bi-collection-fill text-primary"></i> Packs Preventivos</h2>

        @php $limit = 4; @endphp

        <div class="row g-4 mb-5">
            @foreach($packs as $pack)
            <div class="col-lg-4 col-md-6">
                <article class="card card-pack p-4 shadow-sm border-0 h-100 rounded-4 d-flex flex-column transition-hover">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <span class="badge {{ $loop->first ? 'bg-primary' : 'bg-secondary bg-opacity-10 text-secondary' }} rounded-pill px-3 py-2">
                            {{ $loop->first ? 'MÁS SOLICITADO' : 'PACK RECOMENDADO' }}
                        </span>
                        @if($pack->post_id && $pack->post)
                            <a href="{{ route('blog.show', $pack->post->slug) }}" class="info-pill text-decoration-none shadow-sm">
                                <span class="info-pill-icon"><i class="bi bi-journal-medical"></i></span>
                                <span class="info-pill-text">Info</span>
                            </a>
                        @endif
                    </div>

                    <h3 class="h4 fw-bold mb-1 text-dark">{{ $pack->name }}</h3>
                    @if($pack->description)
                        <p class="text-muted small mb-3 fst-italic">{{ $pack->description }}</p>
                    @endif

                    <div class="flex-grow-1 mb-4">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($pack->children->take($limit) as $child)
                                <div class="exam-chip bg-white border small px-2 py-1 rounded-3">
                                    <i class="bi bi-check-circle-fill text-success"></i> {{ $child->name }}
                                </div>
                            @endforeach
                            @if($pack->children->count() > $limit)
                                <div class="exam-chip bg-light border small px-2 py-1 rounded-3 text-secondary fw-bold">
                                    + {{ $pack->children->count() - $limit }} más
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="pt-4 border-top mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div><span class="text-muted small d-block">Total Pack</span></div>
                            <div class="text-end">
                                <span class="price-text fs-3 fw-bold text-primary">${{ number_format($pack->base_price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <a href="{{ route('order.flow', ['type' => 'pack', 'id' => $pack->id]) }}" class="btn btn-primary w-100 py-3 fw-bold rounded-3">
                            Seleccionar Pack <i class="bi bi-chevron-right ms-1"></i>
                        </a>
                    </div>
                </article>
            </div>
            @endforeach
        </div>

        {{-- EXÁMENES INDIVIDUALES --}}
        <div id="frecuentes" class="mb-5">
            <h2 class="fw-bold mb-4 h4"><i class="bi bi-star-fill text-warning"></i> Exámenes Frecuentes</h2>
            <div class="row g-3">
                @foreach($individuales as $exam)
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <article class="card h-100 shadow-sm border-0 p-3 rounded-4">
                        <div class="d-flex flex-column h-100">
                            <h3 class="h6 fw-bold mb-2 text-dark">{{ $exam->name }}</h3>
                            <div class="mt-auto d-flex justify-content-between align-items-center pt-2">
                                <span class="text-primary fw-bold">${{ number_format($exam->base_price, 0, ',', '.') }}</span>
                                <a href="{{ route('order.flow', ['type' => 'exam', 'id' => $exam->id]) }}" class="btn btn-outline-primary btn-sm px-3 rounded-pill">Pedir</a>
                            </div>
                        </div>
                    </article>
                </div>
                @endforeach
            </div>
        </div>

        {{-- BUSCADOR LIVEWIRE --}}
        <div class="bg-white p-4 p-md-5 rounded-5 shadow-sm border border-primary border-opacity-10">
            <div class="text-center mb-4">
                <h2 class="h4 fw-bold">¿Necesitas otro examen?</h2>
                @livewire('exam-search')
            </div>
        </div>
    </div>
</section>

<style>
    .transition-hover { transition: all 0.3s ease; border: 1px solid transparent !important; }
    .transition-hover:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.12) !important; border-color: rgba(13, 110, 253, 0.2) !important; }
    .info-pill { background: rgba(13, 110, 253, 0.06); border: 1px solid rgba(13, 110, 253, 0.15); border-radius: 50px; padding: 3px 10px 3px 4px; display: flex; align-items: center; }
    .info-pill-icon { background: #0d6efd; color: white; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin-right: 6px; font-size: 0.7rem; }
    .info-pill-text { font-size: 0.65rem; font-weight: 800; color: #0d6efd; }
</style>
@endsection
