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

        .hero-section { padding: 140px 0 100px; background: radial-gradient(circle at 80% 20%, #e7f1ff 0%, #ffffff 50%); }

        .card-examen { transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); border: 1px solid #edf2f7; border-radius: 24px; background: #fff; position: relative; overflow: hidden; }
        .card-examen:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06); border-color: var(--primary-color); }

        /* Estilo para la Card "No encontré mi examen" */
        .card-custom-order { background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); color: white; border: none; }
        .card-custom-order .price-tag, .card-custom-order .card-title, .card-custom-order .text-muted { color: white !important; }
        .card-custom-order .icon-box { background: rgba(255,255,255,0.2); color: white; }
        .card-custom-order .btn-select { background: white; color: #0d6efd; border: none; }
        .card-custom-order .btn-select:hover { background: #f8f9fa; transform: scale(1.02); }

        .price-tag { font-size: 1.75rem; font-weight: 800; color: #000; letter-spacing: -0.5px; }
        .btn-select { border-radius: 14px; font-weight: 700; padding: 12px; transition: all 0.3s; }

        .icon-box { width: 48px; height: 48px; background: var(--soft-bg); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-color); margin-bottom: 1.5rem; }

        .toast-container-custom { position: fixed; top: 90px; right: 20px; z-index: 2000; max-width: 350px; }
        .badge-promo { background-color: #eef2ff; color: #4338ca; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
</head>
<body>

    <div class="toast-container-custom">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-lg border-0 rounded-4" role="alert">
                <div class="d-flex">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div><strong>¡Éxito!</strong><br>{{ session('success') }}</div>
                </div>
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

            <div class="ms-auto d-flex align-items-center">
                @auth
                    <div class="dropdown">
                        <button class="btn btn-light border dropdown-toggle px-3 py-2 rounded-4 shadow-sm" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-2 text-primary"></i>
                            <span class="fw-semibold">{{ explode(' ', auth()->user()->name)[0] }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 p-2 rounded-4">
                            <li><a class="dropdown-item rounded-3 py-2" href="{{ route('patient.orders') }}">
                                <i class="bi bi-file-earmark-medical me-2"></i> Mis Órdenes
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item rounded-3 py-2 text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary px-4 py-2 fw-bold shadow-sm rounded-4">
                        Iniciar Sesión
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <header class="hero-section">
        <div class="container text-center">
            <span class="badge badge-promo py-2 px-3 rounded-pill mb-3">Disponible en todo Chile</span>
            <h1 class="display-3 fw-800 text-dark mb-4" style="font-weight: 800; letter-spacing: -2px;">
                Tu Orden Médica, <br><span class="text-primary">al Instante.</span>
            </h1>
            <p class="lead text-muted mx-auto mb-5" style="max-width: 650px; font-size: 1.2rem;">
                Sin agendas ni salas de espera. Obtén tu orden para exámenes de laboratorio firmada por médicos colegiados en minutos.
            </p>
        </div>
    </header>

    <section id="examenes" class="py-5 bg-light">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="fw-bold h1 mb-2">Exámenes Disponibles</h2>
                <p class="text-muted">Selecciona el examen que necesitas o solicita uno personalizado</p>
            </div>

            <div class="row g-4">
                {{-- Ciclo de exámenes existentes --}}
                @foreach($packs as $pack)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 card-examen p-4 border-0">
                        <div class="card-body d-flex flex-column p-0">
                            <div class="icon-box">
                                <i class="bi bi-clipboard2-pulse fs-4"></i>
                            </div>
                            <h4 class="card-title fw-bold mb-3">{{ $pack->name }}</h4>
                            <p class="card-text text-muted small flex-grow-1">
                                {{ $pack->description ?? 'Examen válido para laboratorios a nivel nacional.' }}
                            </p>
                            <div class="mt-4 pt-4 border-top">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="text-muted small fw-semibold">Valor Final</div>
                                    <div class="price-tag">${{ number_format($pack->base_price, 0, ',', '.') }}</div>
                                </div>
                                <button class="btn btn-primary w-100 btn-select shadow-sm" onclick="window.location.href='{{ route('orders.confirm', $pack->id) }}'">
                                    Seleccionar <i class="bi bi-chevron-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                {{-- CARD DE CAPTURA: NO ENCONTRÉ MI EXAMEN --}}
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 card-examen card-custom-order p-4 shadow-lg">
                        <div class="card-body d-flex flex-column p-0 text-white">
                            <div class="icon-box shadow-sm">
                                <i class="bi bi-magic fs-4"></i>
                            </div>
                            <h4 class="card-title fw-bold mb-3">¿No encontraste tu examen?</h4>
                            <p class="card-text small flex-grow-1 opacity-90">
                                Dinos qué examen necesitas. Un médico revisará tu solicitud y emitirá la orden correspondiente de forma personalizada.
                            </p>
                            <div class="mt-4 pt-4 border-top border-white border-opacity-25">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="small fw-semibold opacity-75">Desde</div>
                                    <div class="price-tag">$9.990</div>
                                </div>
@auth
    {{-- Usuario logueado: Va directo al formulario de solicitud especial --}}
    <button class="btn btn-select w-100 shadow-sm" 
            onclick="window.location.href='{{ route('orders.custom') }}'">
        Solicitar a Medida <i class="bi bi-pencil-square ms-1"></i>
    </button>
@else
    {{-- Usuario NO logueado: Salta directo al login de Google --}}
    <button class="btn btn-select w-100 shadow-sm" 
            onclick="window.location.href='{{ route('auth.google') }}'">
        <i class="bi bi-google me-2"></i> Solicitar con Google
    </button>
@endauth


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
