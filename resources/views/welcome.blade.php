<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedOrder Flow - Órdenes Médicas al Instante</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root { --primary-color: #0d6efd; --soft-bg: #f8faff; }
        body { font-family: 'Inter', sans-serif; scroll-behavior: smooth; background-color: #ffffff; color: #212529; }
        .navbar { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
        .sticky-nav { position: sticky; top: 0; z-index: 1020; }

        /* Hero Section */
        .hero-section { padding: 80px 0 60px; background: radial-gradient(circle at 80% 20%, #e7f1ff 0%, #ffffff 50%); }

        /* Cards */
        .card-examen { transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); border: 1px solid #edf2f7; border-radius: 24px; background: #fff; position: relative; }
        .card-examen:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06); border-color: var(--primary-color); }

        .card-pack { border-top: 5px solid var(--primary-color); }
        .card-individual { background-color: #fafbfc; }
        .card-custom-order { background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); color: white; border: none; }

        .price-tag { font-size: 1.5rem; font-weight: 800; color: #000; letter-spacing: -0.5px; }
        .card-custom-order .price-tag { color: white !important; }

        .btn-select { border-radius: 14px; font-weight: 700; padding: 12px; transition: all 0.3s; text-decoration: none; display: flex; align-items: center; justify-content: center; }

        .icon-box { width: 48px; height: 48px; background: var(--soft-bg); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-color); margin-bottom: 1rem; }
        .card-custom-order .icon-box { background: rgba(255,255,255,0.2); color: white; }

        /* Badge para los items de un pack */
        .pack-item { font-size: 0.75rem; background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 8px; font-weight: 600; display: inline-block; margin: 2px; }

        .section-title { font-weight: 800; letter-spacing: -1px; margin-bottom: 2rem; }
    </style>
</head>
<body>

    {{-- Notificaciones --}}
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 2000; margin-top: 70px;">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-lg border-0 rounded-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <nav class="navbar navbar-expand-lg navbar-light sticky-nav border-bottom shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-extrabold text-primary fs-3 d-flex align-items-center" href="{{ route('home') }}">
                <i class="bi bi-droplet-fill me-2"></i>
                <span style="letter-spacing: -1px;">MedOrder<span class="text-dark">Flow</span></span>
            </a>

            <div class="ms-auto">
                @auth
                    <div class="dropdown">
                        <button class="btn btn-white border dropdown-toggle px-3 py-2 rounded-4 shadow-sm fw-bold" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1 text-primary"></i>
                            {{ explode(' ', auth()->user()->name)[0] }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 p-2 rounded-4">
                            <li><a class="dropdown-item rounded-3 py-2" href="{{ route('patient.orders') }}"><i class="bi bi-file-earmark-medical me-2 text-primary"></i> Mis Órdenes</a></li>
                            <li><a class="dropdown-item rounded-3 py-2" href="{{ route('patient.circle') }}"><i class="bi bi-people me-2 text-primary"></i> Mi Círculo</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item rounded-3 py-2 text-danger"><i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('auth.google') }}" class="btn btn-outline-primary px-4 py-2 fw-bold shadow-sm rounded-4">
                        <i class="bi bi-google me-2"></i> Acceder
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <header class="hero-section text-center">
        <div class="container">
            <span class="badge bg-light text-primary py-2 px-3 rounded-pill mb-3 border">100% Online · Firma Digital · Todo Chile</span>
            <h1 class="display-3 fw-bold text-dark mb-4" style="letter-spacing: -2px;">
                Tus Exámenes, <br><span class="text-primary">Sin Esperas.</span>
            </h1>
            <p class="lead text-muted mx-auto mb-5" style="max-width: 650px;">
                Selecciona tu examen o pack, completa tus datos y recibe tu orden médica firmada directamente en tu perfil.
            </p>
        </div>
    </header>

    <section class="py-5 bg-light">
        <div class="container">

            {{-- 1. SECCIÓN PACKS PREVENTIVOS --}}
            <h3 class="section-title text-center text-md-start"><i class="bi bi-collection-play text-primary me-2"></i> Packs Preventivos</h3>
            <div class="row g-4 mb-5">
                @foreach($packs as $pack)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 card-examen card-pack p-4 border-0 shadow-sm">
                        <div class="card-body d-flex flex-column p-0">
                            <div class="icon-box">
                                <i class="bi bi-layers-fill fs-4"></i>
                            </div>
                            <h4 class="card-title fw-bold mb-3">{{ $pack->name }}</h4>

                            {{-- Visualización de items del Pack --}}
                            <div class="mb-4 flex-grow-1">
                                <p class="text-muted small mb-2 fw-bold">Incluye los siguientes exámenes:</p>
                                @foreach($pack->children as $child)
                                    <span class="pack-item"><i class="bi bi-check2 me-1"></i>{{ $child->name }}</span>
                                @endforeach
                            </div>

                            <div class="mt-3 pt-4 border-top">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <span class="text-muted small fw-semibold">Valor Total</span>
                                    <span class="price-tag">${{ number_format($pack->base_price, 0, ',', '.') }}</span>
                                </div>
                                <a href="{{ route('order.flow', ['type' => 'pack', 'id' => $pack->id]) }}" class="btn btn-primary w-100 btn-select shadow-sm">
                                    Seleccionar Pack <i class="bi bi-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <hr class="my-5 opacity-10">

            {{-- 2. SECCIÓN EXÁMENES INDIVIDUALES Y PERSONALIZADOS --}}
            <div class="row g-4">
                <div class="col-lg-8">
                    <h3 class="section-title"><i class="bi bi-search text-primary me-2"></i> Exámenes Frecuentes</h3>
                    <div class="row g-3">
                        {{-- Aquí podrías loopear exámenes individuales si los envías desde el controlador --}}
                        {{-- Ejemplo estático basado en tu requerimiento de "sueltos" --}}
                        @foreach($individualExams ?? [] as $exam)
                        <div class="col-md-6">
                            <div class="card card-examen card-individual p-3 border-0 shadow-sm">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="fw-bold mb-0">{{ $exam->name }}</h6>
                                        <small class="text-primary fw-bold">${{ number_format($exam->base_price, 0, ',', '.') }}</small>
                                    </div>
                                    <a href="{{ route('order.flow', ['type' => 'exam', 'id' => $exam->id]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        Solicitar
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-4">
                    <h3 class="section-title">Especial</h3>
                    <div class="card h-100 card-examen card-custom-order p-4 shadow-lg">
                        <div class="card-body d-flex flex-column p-0">
                            <div class="icon-box shadow-sm">
                                <i class="bi bi-magic fs-4"></i>
                            </div>
                            <h4 class="card-title fw-bold mb-2 text-white">¿Buscas algo más?</h4>
                            <p class="card-text small flex-grow-1 opacity-90">
                                Si no encuentras tu examen en la lista, solicítalo aquí. Un médico revisará tu requerimiento.
                            </p>
                            <div class="mt-4 pt-4 border-top border-white border-opacity-25">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <span class="small fw-semibold opacity-75">Desde</span>
                                    <span class="price-tag text-white">$9.990</span>
                                </div>
                                <a href="{{ route('order.flow', ['type' => 'personalizada']) }}" class="btn btn-light w-100 btn-select text-primary shadow-sm">
                                    Orden Personalizada <i class="bi bi-pencil-square ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <footer class="py-5 text-center text-muted border-top bg-white">
        <p class="small mb-0">&copy; {{ date('Y') }} MedOrder Flow. Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
