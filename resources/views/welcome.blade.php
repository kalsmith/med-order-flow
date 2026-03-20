@extends('layouts.front')

@section('title', config('app.name') . ' - Órdenes Médicas al Instante')

@section('meta')
    {{-- Open Graph / Facebook / WhatsApp --}}
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_CL">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="PideTuExamen">
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


{{-- SECCIÓN DE PRODUCTOS --}}
<section id="packs" class="py-5 bg-light rounded-5 mx-2 shadow-inner">
    <div class="container">
        @include('front.partials.pack-card')
        @include('front.partials.banner-custom')
        @include('front.partials.individual-exams')
         @include('front.partials.search-exams')
    </div>
</section>

{{-- JSON-LD de Organización Médica (SEO) --}}
@php
$schema = [
  "@context" => "https://schema.org",
  "@type" => "MedicalOrganization",
  "name" => "PideTuExamen",
  "url" => url('/'),
  "logo" => asset('assets/img/logo.png'),
  "contactPoint" => [
    "@type" => "ContactPoint",
    "telephone" => "+56-XXXXXXXXX",
    "contactType" => "customer service",
    "areaServed" => "CL",
    "availableLanguage" => "Spanish"
  ],
  "description" => "Servicio online de órdenes médicas oficiales en Chile, válidas para Fonasa e Isapre."
];
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>


@endsection
