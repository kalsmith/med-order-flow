<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Círculo - MedOrder Flow</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    @livewireStyles
    <style>
        :root { --primary-color: #0d6efd; --soft-bg: #f8faff; }
        body { font-family: 'Inter', sans-serif; background-color: #f8faff; color: #212529; }
        .navbar { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
        .sticky-nav { position: sticky; top: 0; z-index: 1020; }
        .card-circle { border-radius: 24px; border: none; overflow: hidden; }
        .member-row { transition: all 0.2s; border-radius: 16px; margin-bottom: 8px; border: 1px solid #f1f5f9 !important; }
        .member-row:hover { background-color: #f8faff; border-color: var(--primary-color) !important; }
        .avatar-circle { width: 48px; height: 48px; background: #e7f1ff; color: #0d6efd; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }

        /* Estilos para el Modal de Livewire */
        .modal-backdrop-custom { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); backdrop-filter: blur(4px); z-index: 1050; }
        .custom-modal { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1060; width: 100%; max-width: 500px; }
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
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 p-2 rounded-4" style="min-width: 200px;">
                            <div class="px-3 py-2 mb-1">
                                <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.65rem;">Mi Gestión</span>
                            </div>
                            <li><a class="dropdown-item rounded-3 py-2" href="{{ route('patient.orders') }}"><i class="bi bi-file-earmark-medical me-2 text-primary"></i> Mis Órdenes</a></li>
                            <li><a class="dropdown-item rounded-3 py-2" href="{{ route('patient.circle') }}"><i class="bi bi-people me-2 text-primary"></i> Mi Círculo</a></li>
                            <li><hr class="dropdown-divider opacity-50"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item rounded-3 py-2 text-danger border-0 bg-transparent w-100 text-start">
                                        <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <main class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    @livewire('patient.circle-manager')

                    <div class="mt-4 p-4 rounded-4 bg-white border shadow-sm">
                        <div class="d-flex align-items-center text-primary">
                            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                            <p class="small mb-0 text-dark">
                                <strong>Importante:</strong> Los datos de tu círculo se utilizan para generar las órdenes médicas. Asegúrate de que el RUT y nombre coincidan con la cédula de identidad del paciente.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
