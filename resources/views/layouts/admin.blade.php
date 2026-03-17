<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'PideTuExamen Admin') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
:root {
    --sidebar-bg: #1e293b;
    --sidebar-active: #334155;
    --accent-color: #3b82f6;
    --main-bg: #f1f5f9;
}

body {
    font-family: 'Inter', sans-serif;
    font-size: .8125rem; /* Un pelín más pequeña para ganar aire */
    background-color: var(--main-bg);
    color: #334155;
    margin: 0;
}

/* Navbar */
.navbar {
    background-color: #ffffff !important;
    border-bottom: 1px solid #e2e8f0;
    height: 65px; /* Reducida de 70px */
    z-index: 1050;
    position: sticky;
    top: 0;
}

/* Sidebar - Sticky para evitar cortes */
.sidebar {
    position: sticky;
    top: 0;
    height: 100vh;
    z-index: 1040;
    width: 250px; /* Un poco más angosta */
    background: var(--sidebar-bg);
    transition: transform 0.3s ease;
    overflow-y: visible;
}

.sidebar-sticky {
    padding: 75px 0 20px 0;
}

.sidebar .nav-link {
    font-size: 0.85rem; /* Fuente más estilizada */
    font-weight: 500;
    color: #94a3b8;
    padding: 0.6rem 1.2rem; /* Más compacto verticalmente */
    margin: 2px 10px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    text-decoration: none;
    transition: all 0.2s ease-in-out;
}

.sidebar .nav-link i {
    font-size: 1rem;
    margin-right: 10px;
    opacity: 0.8;
}

.sidebar .nav-link:hover {
    color: #f8fafc;
    background: var(--sidebar-active);
    padding-left: 1.4rem; /* El toque de movimiento que mencionamos */
}

.sidebar .nav-link.active {
    color: #fff !important;
    background: var(--accent-color) !important;
    box-shadow: 0 4px 12px -2px rgba(59, 130, 246, 0.25);
}

.sidebar-heading {
    padding: 1rem 1.4rem 0.4rem; /* Menos espacio entre secciones */
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #475569; /* Un poco más oscuro para mejor contraste */
    font-weight: 700;
}

/* Main Content Wrapper */
.main-wrapper {
    display: flex;
    align-items: flex-start;
}

main {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.content-header { background: white; padding: 18px 35px; border-bottom: 1px solid #e2e8f0; }
.content-body { flex: 1; padding: 25px 35px; }
.footer { background: white; padding: 15px 35px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 0.75rem; }

/* Móvil - Drawer */
@media (max-width: 767.98px) {
    .sidebar {
        position: fixed;
        bottom: 0;
        transform: translateX(-250px);
        overflow-y: auto;
    }
    .sidebar.show { transform: translateX(0); }
    main { margin-left: 0; }

    .sidebar-backdrop {
        position: fixed;
        top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(2px);
        z-index: 1035;
        display: none;
    }
    body.sidebar-open .sidebar-backdrop { display: block; }
    body.sidebar-open { overflow: hidden; }
}

.avatar-admin { width: 30px; height: 30px; border-radius: 50%; border: 1.5px solid #e2e8f0; }
    </style>
    @stack('css')
</head>
<body>

    <header class="navbar navbar-light sticky-top shadow-sm px-0">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('admin.panel') }}">
                <img src="{{ asset('assets/logo/logo.png') }}" alt="Logo" style="max-height: 40px;" class="ms-3">
            </a>

            <button class="navbar-toggler d-md-none border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                <i class="bi bi-list fs-1"></i>
            </button>

            <div class="ms-auto me-3">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle fw-semibold text-dark d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0D6EFD&color=fff" class="avatar-admin">
                        <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li class="px-3 py-2 small text-muted border-bottom">
                            <div class="fw-bold text-dark">{{ Auth::user()->name }}</div>
                            <div>{{ Auth::user()->getRoleNames()->first() }}</div>
                        </li>
                        <li><a class="dropdown-item py-2" href="/"><i class="bi bi-house-door me-2"></i> Ver Sitio</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger py-2">
                                    <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div class="main-wrapper">
        @include('admin.partials._sidebar')

        <main>
            <div class="content-header d-flex justify-content-between align-items-center">
                <h1 class="h4 m-0 fw-bold text-dark">@yield('header', 'Panel de Control')</h1>
                <div>@yield('header-actions')</div>
            </div>

            <div class="content-body">
                @if (session('success') || session('status'))
                    <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') ?? session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @yield('content')
            </div>

            <footer class="footer mt-auto">
                <div class="d-flex justify-content-between align-items-center">
                    <span>&copy; {{ date('Y') }} <strong>PideTuExamen</strong></span>
                    <span class="badge bg-light text-muted border fw-normal">Chile v2.1</span>
                </div>
            </footer>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebarMenu');
            const toggler = document.querySelector('.navbar-toggler');

            // Backdrop dinámico
            const backdrop = document.createElement('div');
            backdrop.className = 'sidebar-backdrop';
            document.body.appendChild(backdrop);

            // Sincronizar clases de Bootstrap con nuestro estado personalizado
            sidebar.addEventListener('show.bs.collapse', () => document.body.classList.add('sidebar-open'));
            sidebar.addEventListener('hide.bs.collapse', () => document.body.classList.remove('sidebar-open'));

            backdrop.addEventListener('click', () => {
                const bsCollapse = bootstrap.Collapse.getInstance(sidebar);
                if (bsCollapse) bsCollapse.hide();
            });
        });
    </script>
    @stack('js')
</body>
</html>
