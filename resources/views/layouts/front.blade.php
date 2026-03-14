<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @hasSection('title')
            @yield('title') | {{ config('app.name', 'PideTuExamen') }}
        @else
            {{ config('app.name', 'PideTuExamen') }} - Órdenes Médicas al Instante
        @endif
    </title>


    {{-- Bootstrap & Icons (Actualizado a v1.11.3 para soportar todos los iconos médicos) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('css/front-custom.css') }}">

    {{-- En el <head> de tu layout --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('/site.webmanifest') }}">

    @stack('styles')
    @livewireStyles
</head>
<body>

{{-- NAVEGACIÓN --}}
<nav class="navbar navbar-expand-lg navbar-light sticky-top border-bottom bg-white">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <img src="{{ asset('assets/logo/logo.png') }}"
                 alt="PideTuExamen Logo"
                 height="50"
                 class="d-inline-block align-top">
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

    {{-- FOOTER COMPONENTE --}}
    <x-footer />

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
