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

        html, body { height: 100%; }

        body {
            display: flex;
            flex-direction: column;
            font-family: 'Inter', sans-serif;
            font-size: .875rem;
            background-color: var(--main-bg);
            color: #334155;
            overflow-x: hidden;
        }

        /* Navbar */
        .navbar {
            background-color: #ffffff !important;
            border-bottom: 1px solid #e2e8f0;
            height: 70px;
            z-index: 1040;
            padding: 0;
        }

        /* Sidebar - Estructura Fija */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1030;
            width: 260px;
            background: var(--sidebar-bg);
            padding-top: 70px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-sticky {
            height: 100%;
            overflow-y: auto;
            padding: 10px 0 30px 0;
        }

        /* Links del Sidebar */
        .sidebar .nav-link {
            font-weight: 500;
            color: #94a3b8;
            padding: 0.75rem 1.5rem;
            margin: 4px 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        }

        .sidebar .nav-link i { font-size: 1.1rem; margin-right: 12px; }

        .sidebar .nav-link:hover {
            color: #f8fafc;
            background: var(--sidebar-active);
        }

        .sidebar .nav-link.active {
            color: #fff !important;
            background: var(--accent-color) !important;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
        }

        .sidebar-heading {
            padding: 1.5rem 1.5rem 0.5rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            font-weight: 700;
        }

        /* Main Content */
        .main-wrapper { display: flex; flex: 1; }

        main {
            margin-left: 260px;
            display: flex;
            flex-direction: column;
            width: 100%;
            min-height: calc(100vh - 70px);
            transition: all 0.3s;
        }

        .content-header { background: white; padding: 20px 40px; border-bottom: 1px solid #e2e8f0; }
        .content-body { flex: 1 0 auto; padding: 30px 40px; }
        .footer { background: white; padding: 20px 40px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 0.8rem; }

        /* Responsive & Drawer */
        @media (max-width: 767.98px) {
            .sidebar { margin-left: -260px; }
            .sidebar.show { margin-left: 0; }
            main { margin-left: 0; }
            .content-header, .content-body, .footer { padding: 20px; }

            .sidebar-backdrop {
                position: fixed;
                top: 0; left: 0; width: 100vw; height: 100vh;
                background: rgba(15, 23, 42, 0.5);
                backdrop-filter: blur(2px);
                z-index: 1020;
                display: none;
            }
            body.sidebar-open .sidebar-backdrop { display: block; }
        }

        .avatar-admin { width: 32px; height: 32px; border-radius: 50%; border: 2px solid #e2e8f0; }
    </style>
    @stack('css')
</head>
<body class="">

    <header class="navbar navbar-light sticky-top shadow-sm">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-4 d-flex align-items-center" href="{{ route('admin.panel') }}">
            <img src="{{ asset('assets/logo/logo.png') }}" alt="Logo" style="max-height: 40px;">
        </a>

        <button class="navbar-toggler position-absolute d-md-none border-0" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" style="right: 1rem; top: 1.25rem;">
            <i class="bi bi-list fs-2"></i>
        </button>

        <div class="navbar-nav w-100 d-flex flex-row justify-content-end px-4">
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
                            <button type="submit" class="dropdown-item text-danger py-2"><i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión</button>
                        </form>
                    </li>
                </ul>
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
            const backdrop = document.createElement('div');
            backdrop.className = 'sidebar-backdrop';
            document.body.appendChild(backdrop);

            toggler.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
            backdrop.addEventListener('click', () => {
                bootstrap.Collapse.getInstance(sidebar).hide();
                document.body.classList.remove('sidebar-open');
            });
        });
    </script>
    @stack('js')
</body>
</html>
