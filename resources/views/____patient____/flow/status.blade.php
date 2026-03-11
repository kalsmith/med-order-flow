<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | MedOrder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; display: flex; align-items: center; min-height: 100vh; font-family: sans-serif; }
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .icon-box { width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin: 0 auto 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 text-center">
                <div class="card p-4">
                    <div class="card-body">
                        @php
                            $colors = [
                                'error' => 'bg-danger-subtle text-danger',
                                'warning' => 'bg-warning-subtle text-warning',
                                'info' => 'bg-info-subtle text-info'
                            ];
                            $icons = [
                                'error' => 'bi-x-circle-fill',
                                'warning' => 'bi-exclamation-triangle-fill',
                                'info' => 'bi-info-circle-fill'
                            ];
                        @endphp

                        <div class="icon-box {{ $colors[$status] ?? 'bg-secondary-subtle' }}">
                            <i class="bi {{ $icons[$status] ?? 'bi-app' }} fs-2"></i>
                        </div>

                        <h4 class="fw-bold">{{ $title }}</h4>
                        <p class="text-muted">{{ $message }}</p>

                        <div class="d-grid mt-4">
                            <a href="{{ route('patient.orders') }}" class="btn btn-dark py-2 rounded-3">
                                Ir a Mis Órdenes
                            </a>
                            <a href="{{ route('home') }}" class="btn btn-link text-decoration-none mt-2 text-muted small">
                                Volver al Inicio
                            </a>
                        </div>
                    </div>
                </div>
                <p class="mt-4 text-secondary small">MedOrder Flow &copy; 2026</p>
            </div>
        </div>
    </div>
</body>
</html>
