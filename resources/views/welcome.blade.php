@extends('layouts.front')

@section('title', config('app.name') . ' - Órdenes Médicas al Instante')

@section('meta')
    {{-- Configuración Básica --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="author" content="PideTuExamen">

    {{-- SEO Standard --}}
    <meta name="description" content="Obtén tu orden oficial firmada por médicos colegiados, válida para Fonasa, Isapre y todos los laboratorios de Chile. Proceso 100% online en minutos.">
    <meta name="keywords" content="orden medica online, examenes de sangre chile, orden medica fonasa, orden medica isapre, orden de examen rapida">

    {{-- Open Graph / Facebook / WhatsApp --}}
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_CL">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="PideTuExamen">
    <meta property="og:title" content="PideTuExamen - Tu orden médica lista en minutos">
    <meta property="og:description" content="Obtén tu orden oficial firmada por médicos colegiados en Chile. Válida en todos los laboratorios. 100% online.">
    <meta property="og:image" content="{{ asset('assets/img/og-main.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    {{-- Twitter / X --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url('/') }}">
    <meta name="twitter:title" content="PideTuExamen - Tu orden médica lista en minutos">
    <meta name="twitter:description" content="Evita filas. Obtén tu orden oficial firmada por médicos en Chile de forma digital.">
    <meta name="twitter:image" content="{{ asset('assets/img/og-main.jpg') }}">

    {{-- Favicons --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
@endsection

@section('content')
    @include('front.partials.hero')
    @include('front.partials.trust-badges')
    @include('front.partials.steps')

    {{-- Sección de Packs y Exámenes --}}
    @include('front.partials.pack-card')
    @include('front.partials.banner-custom')
    @include('front.partials.individual-exams')
    @include('front.partials.search-exams')


@endsection
