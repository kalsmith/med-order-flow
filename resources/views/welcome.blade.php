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
        .hero-section { padding: 100px 0 60px; background: radial-gradient(circle at 80% 20%, #e7f1ff 0%, #ffffff 50%); }
        .card-examen { transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); border: 1px solid #edf2f7; border-radius: 24px; background: #fff; position: relative; overflow: hidden; }
        .card-examen:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06); border-color: var(--primary-color); }
        .card-custom-order { background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); color: white; border: none; }
        .price-tag { font-size: 1.75rem; font-weight: 800; color: #000; letter-spacing: -0.5px; }
        .card-custom-order .price-tag { color: white !important; }
        .btn-select { border-radius: 14px; font-weight: 700; padding: 12px; transition: all 0.3s; text-decoration: none; display: flex; align-items: center; justify-content: center; }
        .icon-box { width: 48px; height: 48px; background: var(--soft-bg); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-color); margin-bottom: 1.5rem; }
        .card-custom-order .icon-box { background: rgba(255,255,255,0.2); color: white; }
    </style>
</head>
<body>

    <div class="position-fixed top-0 end-0 p-3" style="z-index: 2000; margin-top: 70px;">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-lg border-0 rounded-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-lg border-0 rounded-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
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
                    <button class="btn btn-white border dropdown-toggle px-3 py-2 rounded-4 shadow-sm fw-bold" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1 text-primary"></i>
                        {{ explode(' ', auth()->user()->name)[0] }}
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 p-2 rounded-4" style="min-width: 200px;">
                        <div class="px-3 py-2 mb-1">
                            <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.65rem;">Mi Gestión</span>
                        </div>

                        <li>
                            <a class="dropdown-item rounded-3 py-2 d-flex align-items-center" href="{{ route('patient.orders') }}">
                                <i class="bi bi-file-earmark-medical me-2 text-primary"></i>
                                <span>Mis Órdenes</span>
                            </a>
                        </li>

<li>
    <a class="dropdown-item rounded-3 py-2 d-flex align-items-center" href="{{ route('patient.circle') }}">
        <i class="bi bi-people me-2 text-primary"></i>
        <span>Mi Círculo</span>
    </a>
</li>

                        @role('doctor|admin|director_tecnico')
                            <li><hr class="dropdown-divider opacity-50"></li>
                            <div class="px-3 py-2 mb-1">
                                <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.65rem;">Panel Profesional</span>
                            </div>
                            <li>
                                <a class="dropdown-item rounded-3 py-2 d-flex align-items-center" href="{{ route('admin.orders.index') }}">
                                    <i class="bi bi-clipboard-check me-2 text-success"></i>
                                    <span>Gestión Clínica</span>
                                </a>
                            </li>
                        @endrole

                        <li><hr class="dropdown-divider opacity-50"></li>

                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item rounded-3 py-2 text-danger d-flex align-items-center">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    <span>Cerrar Sesión</span>
                                </button>
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


    <header class="hero-section">
        <div class="container text-center">
            <span class="badge bg-light text-primary py-2 px-3 rounded-pill mb-3 border">Disponible en todo Chile</span>
            <h1 class="display-3 fw-bold text-dark mb-4" style="letter-spacing: -2px;">
                Tu Orden Médica, <br><span class="text-primary">al Instante.</span>
            </h1>
            <p class="lead text-muted mx-auto mb-5" style="max-width: 650px;">
                Obtén tu orden médica para exámenes en minutos, 100% online y firmada.
            </p>
        </div>
    </header>

    <section id="examenes" class="py-5 bg-light">
        <div class="container">
            <div class="row g-4">

                {{-- 1. PACKS (Data desde LandingController) --}}
                @foreach($packs as $pack)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 card-examen p-4 border-0 shadow-sm">
                        <div class="card-body d-flex flex-column p-0">
                            <div class="icon-box">
                                <i class="bi bi-clipboard2-pulse fs-4"></i>
                            </div>
                            <h4 class="card-title fw-bold mb-2">{{ $pack->name }}</h4>
                            <p class="card-text text-muted small flex-grow-1">
                                Incluye: {{ $pack->children->pluck('name')->implode(', ') ?: 'Examen completo validado.' }}
                            </p>
                            <div class="mt-4 pt-4 border-top">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <span class="text-muted small fw-semibold">Valor</span>
                                    <span class="price-tag">${{ number_format($pack->base_price, 0, ',', '.') }}</span>
                                </div>
                                {{-- ACTUALIZADO: Apunta al flujo centralizado --}}
                                <a href="{{ route('order.flow', ['type' => 'pack', 'id' => $pack->id]) }}" class="btn btn-primary w-100 btn-select shadow-sm">
                                    Seleccionar <i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                {{-- 2. ORDEN PERSONALIZADA --}}
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 card-examen card-custom-order p-4 shadow-lg">
                        <div class="card-body d-flex flex-column p-0">
                            <div class="icon-box shadow-sm">
                                <i class="bi bi-magic fs-4"></i>
                            </div>
                            <h4 class="card-title fw-bold mb-2 text-white">¿Buscas otro examen?</h4>
                            <p class="card-text small flex-grow-1 opacity-90">
                                Dinos qué necesitas y un médico emitirá tu orden personalizada.
                            </p>
                            <div class="mt-4 pt-4 border-top border-white border-opacity-25">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <span class="small fw-semibold opacity-75">Desde</span>
                                    <span class="price-tag text-white">$9.990</span>
                                </div>
                                {{-- ACTUALIZADO: Apunta al flujo centralizado --}}
                                <a href="{{ route('order.flow', ['type' => 'personalizada']) }}" class="btn btn-light w-100 btn-select text-primary shadow-sm">
                                    Solicitar a Medida <i class="bi bi-pencil-square ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
