@extends('layouts.front')

@section('title', $post->meta_title ?? $post->title)

@section('seo')
    {{-- Meta Tags Pro-SEO --}}
    <title>{{ $post->meta_title ?? $post->title }} | Med Order Flow</title>
    <meta name="description" content="{{ $post->summary }}">
    <link rel="canonical" href="{{ route('blog.show', $post->slug) }}">

    {{-- Open Graph (Facebook / WhatsApp / LinkedIn) --}}
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $post->meta_title ?? $post->title }}">
    <meta property="og:description" content="{{ $post->summary }}">
    <meta property="og:image" content="{{ asset('storage/' . $post->featured_image) }}">
    <meta property="og:url" content="{{ route('blog.show', $post->slug) }}">
    <meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">
@endsection

@section('content')
<article class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                {{-- Breadcrumbs (Navegación SEO) --}}
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb small">
                        <li class="breadcrumb-item"><a href="/">Inicio</a></li>
                        {{-- <li class="breadcrumb-item"><a href="{{ route('blog.index') }}">Blog</a></li> --}}
                        <li class="breadcrumb-item active" aria-current="page text-truncate">{{ $post->title }}</li>
                    </ol>
                </nav>

                {{-- Encabezado --}}
                <header class="mb-5">
                    <h1 class="display-5 fw-bold text-dark mb-3">{{ $post->title }}</h1>
                    <div class="d-flex align-items-center text-muted small">
                        <span class="me-3"><i class="bi bi-calendar3 me-1"></i> {{ $post->published_at->format('d M, Y') }}</span>
                        <span><i class="bi bi-person me-1"></i> Por {{ $post->author->name }}</span>
                    </div>
                </header>

                {{-- Imagen Destacada (1200x630) --}}
{{-- Imagen Destacada (1200x630) --}}
<figure class="mb-5 shadow-sm rounded overflow-hidden">
    <img src="{{ asset('storage/' . $post->featured_image) }}"
         class="img-fluid w-100"
         alt="{{ $post->title }}" {{-- <--- Alt dinámico --}}
         loading="lazy" {{-- <--- Mejora la velocidad de carga --}}
         style="max-height: 500px; object-fit: cover; width: 100%; height: auto;">
</figure>

                {{-- Contenido del Post --}}
                <div class="blog-content mb-5 lh-lg fs-5">
                    {!! $post->content !!}
                </div>

{{-- TARJETA DE CONVERSIÓN (CTA) --}}
@if($post->cta_id && $post->examType)
    <div class="card border-primary border-2 shadow-lg my-5 overflow-hidden rounded-4">
        <div class="row g-0">
            <div class="col-md-4 bg-primary d-flex align-items-center justify-content-center text-white p-4">
                <div class="text-center">
                    <i class="bi bi-cart-check display-1"></i>
                    <h4 class="mt-2 fw-bold">Oferta</h4>
                </div>
            </div>
            <div class="col-md-8 p-4">
                <div class="mb-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">
                        {{ $post->examType->isProfile() ? 'PACK RECOMENDADO' : 'EXAMEN DISPONIBLE' }}
                    </span>
                </div>
                <h3 class="fw-bold text-dark">{{ $post->examType->name }}</h3>
                <p class="text-muted small mb-4">
                    {{ Str::limit($post->examType->description, 150) }}
                </p>

                <div class="d-flex align-items-center justify-content-between mt-auto">
                    <div>
                        <span class="text-muted small d-block">Precio Web</span>
                        <span class="h2 fw-bold text-primary mb-0">
                            ${{ number_format($post->examType->price ?? $post->examType->base_price, 0, ',', '.') }}
                        </span>
                    </div>

                    {{-- Usamos la ruta order.flow con la lógica de tipo dinámica --}}
                    <a href="{{ route('order.flow', [
                        'type' => $post->examType->isProfile() ? 'pack' : 'exam',
                        'id' => $post->examType->id
                    ]) }}" class="btn btn-primary btn-lg px-4 fw-bold shadow-sm rounded-pill">
                        Reservar Ahora <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif

                <hr class="my-5">

                {{-- Botones para Compartir --}}
                <div class="d-flex align-items-center gap-3">
                    <span class="fw-bold small text-uppercase">Compartir:</span>
                    <a href="https://wa.me/?text={{ urlencode(url()->current()) }}" class="btn btn-outline-success btn-sm rounded-circle"><i class="bi bi-whatsapp"></i></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ url()->current() }}" class="btn btn-outline-primary btn-sm rounded-circle"><i class="bi bi-facebook"></i></a>
                </div>

            </div>
        </div>
    </div>
</article>

<style>
    /* Ajustes para el contenido del editor (CKEditor) */
    .blog-content h2 { font-weight: 700; margin-top: 2rem; color: #0d6efd; }
    .blog-content p { margin-bottom: 1.5rem; }
    .blog-content img { max-width: 100%; height: auto; border-radius: 8px; margin: 1.5rem 0; }
    .blog-content blockquote { border-left: 5px solid #0d6efd; padding-left: 1.5rem; font-style: italic; color: #555; }
</style>
@endsection
