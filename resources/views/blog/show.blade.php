@extends('layouts.front')

{{-- Usamos el @section('title') que espera tu layout --}}
@section('title', $post->meta_title ?? $post->title)

@section('seo')
    {{-- Meta Tags Pro-SEO (Ya no incluimos <title> aquí para evitar duplicados) --}}
    <meta name="description" content="{{ $post->summary }}">
    <link rel="canonical" href="{{ route('blog.show', $post->slug) }}">

    {{-- Open Graph --}}
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

                {{-- Breadcrumbs --}}
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb small bg-light p-2 rounded">
                        <li class="breadcrumb-item"><a href="/" class="text-decoration-none">Inicio</a></li>
                        <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 250px;">
                            {{ $post->title }}
                        </li>
                    </ol>
                </nav>

                {{-- Encabezado --}}
                <header class="mb-4">
                    <h1 class="display-5 fw-bold text-dark mb-3">{{ $post->title }}</h1>
                    <p class="lead text-muted mb-4">{{ $post->summary }}</p> {{-- Mostramos el summary como bajada de título --}}

                    <div class="d-flex align-items-center text-muted small border-top border-bottom py-2">
                        <span class="me-3"><i class="bi bi-calendar3 me-1"></i> {{ $post->published_at->format('d M, Y') }}</span>
                        <span><i class="bi bi-person me-1"></i> Por {{ $post->author->name }}</span>
                    </div>
                </header>

                {{-- Imagen Destacada --}}
                <figure class="mb-5 shadow-sm rounded overflow-hidden">
                    <img src="{{ asset('storage/' . $post->featured_image) }}"
                         class="img-fluid w-100"
                         alt="{{ $post->title }}"
                         loading="eager" {{-- La primera imagen debe cargar rápido --}}
                         style="max-height: 500px; object-fit: cover;">
                </figure>

                {{-- Contenido del Post --}}
                <div class="blog-content mb-5 lh-lg fs-5 text-secondary">
                    {!! $post->content !!}
                </div>

                {{-- TARJETA DE CONVERSIÓN (CTA) --}}
                @if($post->cta_id && $post->examType)
                    <div class="card border-0 shadow-lg my-5 overflow-hidden rounded-4" style="background: linear-gradient(145deg, #ffffff, #f8faff);">
                        <div class="row g-0">
                            <div class="col-md-4 bg-primary d-flex align-items-center justify-content-center text-white p-4">
                                <div class="text-center">
                                    <i class="bi bi-shield-check display-2"></i>
                                    <h4 class="mt-2 fw-bold">Salud al Instante</h4>
                                </div>
                            </div>
                            <div class="col-md-8 p-4 d-flex flex-column">
                                <div class="mb-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2">
                                        <i class="bi bi-info-circle me-1"></i>
                                        {{ $post->examType->isProfile() ? 'PACK MÉDICO RECOMENDADO' : 'EXAMEN DISPONIBLE' }}
                                    </span>
                                </div>
                                <h3 class="fw-bold text-dark">{{ $post->examType->name }}</h3>
                                <p class="text-muted small mb-4 flex-grow-1">
                                    {{ Str::limit($post->examType->description, 160) }}
                                </p>

                                <div class="d-flex align-items-center justify-content-between mt-auto pt-3 border-top">
                                    <div>
                                        <span class="text-muted small d-block">Precio Fonasa/Isapre</span>
                                        <span class="h3 fw-bold text-primary mb-0">
                                            ${{ number_format($post->examType->price ?? $post->examType->base_price, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    <a href="{{ route('order.flow', [
                                        'type' => $post->examType->isProfile() ? 'pack' : 'exam',
                                        'id' => $post->examType->id
                                    ]) }}" class="btn btn-primary btn-lg px-4 fw-bold shadow-sm rounded-pill transition-all hover-lift">
                                        Pedir Orden <i class="bi bi-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <hr class="my-5 opacity-25">

                {{-- Social Share --}}
                <div class="d-flex align-items-center gap-3 bg-light p-3 rounded-pill justify-content-center">
                    <span class="fw-bold small text-uppercase text-muted">¿Te sirvió esta información? Compártela:</span>
                    <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . url()->current()) }}"
                       target="_blank"
                       class="btn btn-success btn-sm rounded-circle shadow-sm"
                       title="Compartir en WhatsApp">
                       <i class="bi bi-whatsapp"></i>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ url()->current() }}"
                       target="_blank"
                       class="btn btn-primary btn-sm rounded-circle shadow-sm"
                       title="Compartir en Facebook">
                       <i class="bi bi-facebook"></i>
                    </a>
                </div>

            </div>
        </div>
    </div>
</article>

<style>
    .blog-content h2, .blog-content h3 { font-weight: 700; margin-top: 2.5rem; color: #1a202c; }
    .blog-content p { margin-bottom: 1.6rem; color: #4a5568; line-height: 1.8; }
    .blog-content img { border-radius: 12px; margin: 2rem 0; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .blog-content blockquote {
        border-left: 5px solid #0d6efd;
        padding: 1rem 1.5rem;
        background-color: #f8f9fa;
        border-radius: 0 8px 8px 0;
        font-style: italic;
    }
    .hover-lift:hover { transform: translateY(-3px); transition: all 0.2s ease; }
</style>
@endsection
