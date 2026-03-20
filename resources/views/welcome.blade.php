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
    @include('front.partials.hero')
    @include('front.partials.trust-badges')
    @include('front.partials.steps')
    @include('front.partials.pack-card')


        @include('front.partials.banner-custom')
        @include('front.partials.individual-exams')



        {{-- BUSCADOR LIVEWIRE --}}
        <div class="bg-white p-4 p-md-5 rounded-5 shadow-sm border border-primary border-opacity-10">
            <div class="text-center mb-4">
                <h4 class="fw-bold">¿Necesitas otro examen?</h4>
                <p class="text-muted">Busca en nuestro catálogo completo de exámenes individuales.</p>
            </div>

            {{-- Aumentamos el max-width a 1200px o lo quitamos para que use el ancho del contenedor --}}
            <div class="mx-auto" style="max-width: 1200px;">
                @livewire('exam-search')
            </div>
        </div>

    </div>
</section>
@endsection
