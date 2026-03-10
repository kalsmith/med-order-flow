<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Órdenes - MedOrder Flow</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #0d6efd;
            --soft-bg: #f8faff;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #fcfdfe;
            color: #212529;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .order-card {
            transition: all 0.3s ease;
            border: 1px solid #edf2f7;
            border-radius: 20px;
            background: #fff;
        }

        .order-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }

        /* Estados de la Orden */
        .bg-pending { background-color: #fff7ed; color: #c2410c; } /* Naranja */
        .bg-paid { background-color: #eff6ff; color: #1d4ed8; }    /* Azul */
        .bg-signed { background-color: #f0fdf4; color: #15803d; }  /* Verde */

        .btn-action {
            border-radius: 12px;
            font-weight: 600;
            padding: 8px 20px;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg sticky-top border-bottom shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-extrabold text-primary fs-3 d-flex align-items-center" href="{{ route('home') }}">
                <i class="bi bi-droplet-fill me-2"></i>
                <span style="letter-spacing: -1px;">MedOrder<span class="text-dark">Flow</span></span>
            </a>
            <div class="ms-auto">
                <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-action border-0">
                    <i class="bi bi-plus-lg me-1"></i> Nueva Orden
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="d-flex align-items-center justify-content-between mb-5">
                    <div>
                        <h1 class="fw-800 mb-1" style="letter-spacing: -1.5px;">Mis Órdenes Médicas</h1>
                        <p class="text-muted mb-0">Gestiona y descarga tus órdenes emitidas.</p>
                    </div>
                </div>

                @forelse($orders as $order)
                <div class="card order-card mb-4 shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 p-3 rounded-4 me-3 text-primary">
                                        <i class="bi bi-file-earmark-medical fs-3"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1">{{ $order->examType->name }}</h5>
                                        <p class="text-muted small mb-0">Solicitado el {{ $order->created_at->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 text-center py-3 py-md-0">
                                @if($order->status === 'signed')
                                    <span class="status-badge bg-signed">
                                        <i class="bi bi-patch-check-fill me-1"></i> Firmada
                                    </span>
                                @elseif($order->status === 'paid')
                                    <span class="status-badge bg-paid">
                                        <i class="bi bi-clock-history me-1"></i> Esperando Firma
                                    </span>
                                @else
                                    <span class="status-badge bg-pending">
                                        <i class="bi bi-hourglass-split me-1"></i> Pendiente Pago
                                    </span>
                                @endif
                            </div>

                            <div class="col-md-4 text-md-end">
                                @if($order->status === 'signed')
                                    <a href="#" class="btn btn-primary btn-action w-100 w-md-auto">
                                        <i class="bi bi-download me-2"></i> Descargar PDF
                                    </a>
                                @else
                                    <button class="btn btn-light btn-action w-100 w-md-auto text-muted" disabled>
                                        <i class="bi bi-lock me-2"></i> No disponible
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-clipboard-x text-muted" style="font-size: 5rem;"></i>
                    </div>
                    <h3>Aún no tienes órdenes</h3>
                    <p class="text-muted mb-4">Selecciona un examen en el inicio para generar tu primera orden médica.</p>
                    <a href="{{ route('home') }}" class="btn btn-primary px-5 py-3 rounded-4 fw-bold shadow">
                        Ir a Ver Exámenes
                    </a>
                </div>
                @endforelse

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
