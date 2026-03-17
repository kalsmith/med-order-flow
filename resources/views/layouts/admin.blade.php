<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'MedOrder Admin') }}</title>

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
            font-size: .875rem;
            background-color: var(--main-bg);
            color: #334155;
        }

        /* Navbar */
        .navbar {
            background-color: #ffffff !important;
            border-bottom: 1px solid #e2e8f0;
            height: 60px;
        }
        .navbar-brand {
            color: var(--sidebar-bg) !important;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        /* Sidebar - Estilos mantenidos y mejorados */
        .sidebar {
            position: fixed; top: 0; bottom: 0; left: 0; z-index: 100;
            width: 260px; background: var(--sidebar-bg);
            transition: all 0.3s;
        }
        .sidebar-sticky {
            position: relative; height: calc(100vh - 60px);
            top: 60px; padding-top: 20px;
            overflow-x: hidden; overflow-y: auto;
        }

        .sidebar .nav-link {
            font-weight: 500; color: #94a3b8;
            padding: 0.75rem 1.5rem; margin: 4px 12px;
            border-radius: 8px; display: flex; align-items: center;
        }
        .sidebar .nav-link i { font-size: 1.1rem; margin-right: 12px; }
        .sidebar .nav-link:hover { color: #f8fafc; background: var(--sidebar-active); }
        .sidebar .nav-link.active {
            color: #fff !important;
            background: var(--accent-color) !important;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
        }

        .sidebar-heading {
            padding: 1.5rem 1.5rem 0.5rem; font-size: 0.7rem;
            text-transform: uppercase; letter-spacing: 1px;
            color: #64748b; font-weight: 700;
        }

        /* Content Area */
        main { margin-left: 260px; }

        .content-header {
            background: white;
            margin-bottom: 24px;
            padding: 20px 40px;
            border-bottom: 1px solid #e2e8f0;
        }

        .card {
            border: none; border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        @media (max-width: 767.98px) {
            .sidebar { margin-left: -260px; }
            main { margin-left: 0; }
            .sidebar.show { margin-left: 0; }
        }
    </style>
    @stack('css')
</head>
<body>

    <header class="navbar navbar-light sticky-top flex-md-nowrap p-0 shadow-sm">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-4 fs-5" href="{{ route('admin.panel') }}">
            <i class="bi bi-droplet-fill text-primary"></i> MedOrder Flow
        </a>
        <button class="navbar-toggler position-absolute d-md-none collapsed border-0" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
            <i class="bi bi-list"></i>
        </button>

        <div class="navbar-nav w-100 d-flex flex-row justify-content-end px-4">
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle fw-semibold text-dark" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                    <li class="px-3 py-2 small text-muted border-bottom">
                        Rol: {{ Auth::user()->getRoleNames()->first() ?? 'Sin Rol' }}
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger py-2">
                                <i class="bi bi-box-arrow-right me-2"></i> Salir
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">

            {{-- LLAMADA AL PARTIAL --}}
            @include('admin.partials._sidebar')

            <main class="col-md-9 ms-sm-auto col-lg-10 p-0">
                <div class="content-header d-flex justify-content-between align-items-center">
                    <h1 class="h4 m-0 fw-bold text-dark">@yield('header', 'Panel')</h1>
                    <div>@yield('header-actions')</div>
                </div>

                <div class="px-4 pb-5">
                    @if (session('success'))
                        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('js')
</body>
</html>
