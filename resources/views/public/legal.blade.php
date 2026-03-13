@extends('layouts.front')

@section('title', $faq->question . ' - MedOrder Flow')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">
            {{-- Breadcrumb para volver fácil --}}
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Legal</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <h1 class="fw-extrabold text-primary mb-4">{{ $faq->question }}</h1>

                    <div class="text-muted lh-lg fs-5">
                        {!! $faq->answer !!}
                    </div>

                    <div class="mt-5 pt-4 border-top text-center">
                        <p class="small text-muted mb-4">Si tienes dudas adicionales sobre este documento, contáctanos.</p>
                        <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-5 fw-bold">
                            Entendido, volver al inicio
                        </a>
                    </div>
                </div>
            </div>

            <p class="text-center mt-4 text-muted small">
                Última actualización: {{ $faq->updated_at->format('d/m/Y') }}
            </p>
        </div>
    </div>
</div>
@endsection
