@extends('layouts.front')

@section('title', 'MedOrder Flow - Órdenes Médicas al Instante')

@section('content')
    {{-- HERO SECTION --}}
    <header class="hero-section text-center py-5">
        <div class="container py-md-5">
            <span class="badge bg-primary bg-opacity-10 text-primary py-2 px-3 rounded-pill mb-4 border border-primary border-opacity-25 fw-bold">
                ✨ 100% Online · Firma Digital · Todo Chile
            </span>
            <h1 class="display-3 hero-title text-dark mb-4 fw-800">
                Tus Exámenes, <br><span class="text-primary">Sin Esperas.</span>
            </h1>
            <p class="lead text-muted mx-auto mb-5" style="max-width: 600px;">
                Obtén tu orden médica oficial firmada por profesionales colegiados en minutos. Válida en todos los laboratorios de Chile.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="#packs" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">Ver Exámenes</a>
                <a href="#como-funciona" class="btn btn-outline-dark btn-lg rounded-pill px-5">¿Cómo funciona?</a>
            </div>
        </div>
    </header>

    {{-- TRUST BADGES --}}
    <section class="py-4 border-top border-bottom bg-white">
        <div class="container">
            <div class="row text-center g-4">
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-shield-check text-success fs-4"></i>
                        <span class="small fw-bold text-muted">Firma Electrónica</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-geo-alt text-danger fs-4"></i>
                        <span class="small fw-bold text-muted">Todo Chile</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-clock-history text-primary fs-4"></i>
                        <span class="small fw-bold text-muted">Entrega en Minutos</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-hospital text-info fs-4"></i>
                        <span class="small fw-bold text-muted">Laboratorios Públicos/Privados</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- PASOS --}}
    <section id="como-funciona" class="py-5">
        <div class="container py-4 text-center">
            <h2 class="fw-bold mb-5">Salud preventiva en 3 pasos</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-list-check fs-3"></i>
                    </div>
                    <h5 class="fw-bold">1. Elige tu Examen</h5>
                    <p class="text-muted small">Selecciona un pack preventivo o solicita una orden personalizada.</p>
                </div>
                <div class="col-md-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-person-badge fs-3"></i>
                    </div>
                    <h5 class="fw-bold">2. Validación Médica</h5>
                    <p class="text-muted small">Nuestros médicos revisan tu solicitud para asegurar la pertinencia clínica.</p>
                </div>
                <div class="col-md-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-file-earmark-pdf fs-3"></i>
                    </div>
                    <h5 class="fw-bold">3. Recibe tu Orden</h5>
                    <p class="text-muted small">Descarga tu PDF firmado y preséntalo directamente en el laboratorio.</p>
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
