<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MedOrder Flow - Órdenes Médicas al Instante')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/front-custom.css') }}">

    <style>
        :root { --primary-color: #0d6efd; }
        body { font-family: 'Inter', sans-serif; color: #334155; }
        .fw-800 { font-weight: 800; }
        .navbar { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
        .btn-primary { border-radius: 12px; padding: 10px 24px; }
        /* Transiciones suaves para links del footer */
        footer a { transition: all 0.3s ease; }
        footer a:hover { color: var(--primary-color) !important; padding-left: 5px; }
    </style>

    @stack('styles')
    @livewireStyles
</head>
<body>

    {{-- NAVEGACIÓN --}}
    <nav class="navbar navbar-expand-lg navbar-light sticky-top border-bottom">
        <div class="container">
            <a class="navbar-brand fw-extrabold text-primary fs-3 d-flex align-items-center" href="/">
                <i class="bi bi-droplet-fill me-2"></i> MedOrderFlow
            </a>

            <div class="ms-auto d-flex align-items-center gap-2">
                @auth
                    <div class="dropdown">
                        <button class="btn btn-light rounded-pill px-4 fw-bold dropdown-toggle d-flex align-items-center gap-2" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=0D6EFD&color=fff&rounded=true" width="24" alt="Avatar">
                            <span class="d-none d-md-inline">Mi Cuenta</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="userMenu" style="border-radius: 15px; min-width: 200px;">
                            <li><h6 class="dropdown-header text-uppercase small opacity-50">Gestión de Salud</h6></li>
                            <li><a class="dropdown-item fw-bold py-2" href="{{ route('patient.orders') }}"><i class="bi bi-file-earmark-medical me-2 text-primary"></i> Mis Órdenes</a></li>
                            <li><a class="dropdown-item fw-bold py-2" href="{{ route('patient.circle') }}"><i class="bi bi-people me-2 text-primary"></i> Mi Círculo Familiar</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger fw-bold py-2">
                                        <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('auth.google') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Ingresar</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- CONTENIDO PRINCIPAL --}}
    <main>
        @yield('content')
    </main>

    {{-- FOOTER TRANSVERSAL --}}
    <footer class="pt-5 pb-4 bg-dark text-white mt-5">
        <div class="container">
            <div class="row g-4 mb-5">
                <div class="col-lg-4">
                    <h4 class="fw-bold text-primary mb-4"><i class="bi bi-droplet-fill"></i> MedOrderFlow</h4>
                    <p class="text-white-50 small">Soluciones médicas digitales para un Chile más sano. Obten tus órdenes de exámenes preventivos de forma legal y segura.</p>
                    <div class="d-flex gap-3 mt-4">
                        <a href="#" class="text-white fs-5"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white fs-5"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="fw-bold mb-4 text-uppercase small text-primary">Servicios</h6>
                    <ul class="list-unstyled small text-white-50">
                        <li class="mb-2"><a href="#packs" class="text-decoration-none text-reset">Packs Preventivos</a></li>
                        <li class="mb-2"><a href="#frecuentes" class="text-decoration-none text-reset">Exámenes Individuales</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-reset">Orden a Medida</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="fw-bold mb-4 text-uppercase small text-primary">Soporte</h6>
                    <ul class="list-unstyled small text-white-50">
                        <li class="mb-2"><a href="#" class="text-decoration-none text-reset">Preguntas Frecuentes</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-reset">Contacto</a></li>
                        <li class="mb-2"><a href="#como-funciona" class="text-decoration-none text-reset">Cómo funciona</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="fw-bold mb-4 text-uppercase small text-primary">Contacto</h6>
                    <ul class="list-unstyled small text-white-50">
                        <li class="mb-2"><i class="bi bi-envelope me-2 text-primary"></i> contacto@medorderflow.cl</li>
                        <li class="mb-2"><i class="bi bi-whatsapp me-2 text-primary"></i> +56 9 1234 5678</li>
                        <li class="mb-2"><i class="bi bi-geo-alt me-2 text-primary"></i> Santiago, Chile</li>
                    </ul>
                </div>
            </div>
            <div class="border-top border-secondary pt-4 text-center">
                <p class="small text-white-50 mb-0">© {{ date('Y') }} MedOrder Flow Chile. Todos los derechos reservados. Registrados en la Superintendencia de Salud.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
