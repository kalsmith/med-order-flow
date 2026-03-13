@extends('layouts.front')

@section('title', $faq->question . ' - MedOrder Flow')

@section('content')
<div class="container py-5 my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Inicio</a></li>
                    <li class="breadcrumb-item active">{{ $faq->question }}</li>
                </ol>
            </nav>

            <h1 class="fw-bold mb-4 text-primary">{{ $faq->question }}</h1>
            <hr class="mb-5">

            <div class="lh-lg text-muted" style="font-size: 1.1rem;">
                {!! nl2br(e($faq->answer)) !!}
            </div>

            <div class="mt-5 pt-4 border-top">
                <a href="/" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i> Volver al inicio
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
