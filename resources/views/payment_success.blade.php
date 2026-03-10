<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Exitoso | MedOrder Flow</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root { --primary-color: #0d6efd; --soft-bg: #f8faff; }
        body { font-family: 'Inter', sans-serif; background-color: #ffffff; color: #212529; }

        .navbar { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
        .sticky-nav { position: sticky; top: 0; z-index: 1020; }

        .card-success { border-radius: 24px; border: 1px solid #edf2f7; }

        @media print {
            .navbar, .btn, .footer, .d-grid { display: none !important; }
            .card { box-shadow: none !important; border: 1px solid #ccc !important; }
            .container { padding: 0 !important; }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light sticky-nav border-bottom shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-extrabold text-primary fs-3 d-flex align-items-center" href="{{ route('home') }}">
                <i class="bi bi-droplet-fill me-2"></i>
                <span style="letter-spacing: -1px;">MedOrder<span class="text-dark">Flow</span></span>
            </a>
            <div class="ms-auto d-flex align-items-center">
                <a href="{{ route('patient.orders') }}" class="btn btn-outline-primary rounded-4">Mis Órdenes</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card card-success border-0 shadow-sm p-4">
                    <div class="card-body text-center p-md-4">

                        <div class="mb-4 d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle" style="width: 80px; height: 80px;">
                            <i class="bi bi-check-lg fs-1"></i>
                        </div>

                        <h2 class="fw-extrabold mb-2" style="letter-spacing: -1px;">¡Pago Exitoso!</h2>
                        <p class="text-muted mb-4">Hemos recibido tu pago correctamente.<br>Tu orden ya está en proceso.</p>

                        <div class="bg-light p-4 rounded-4 text-start mb-4 border">
                            <h6 class="fw-bold border-bottom pb-2 mb-3 text-uppercase small text-secondary">
                                Detalle del Comprobante
                            </h6>

                            <dl class="row mb-0 small">
                                <dt class="col-sm-5 text-muted">Nº Orden:</dt>
                                <dd class="col-sm-7 fw-semibold text-dark">{{ $order->id }}</dd>

                                <dt class="col-sm-5 text-muted">Fecha:</dt>
                                <dd class="col-sm-7">{{ $order->created_at ? $order->created_at->format('d-m-Y H:i') : 'N/A' }}</dd>

                                <dt class="col-sm-5 text-muted">Monto:</dt>
                                <dd class="col-sm-7 text-primary fw-bold fs-6">$ {{ number_format($order->amount, 0, ',', '.') }}</dd>

                                <dt class="col-sm-5 text-muted">Medio de Pago:</dt>
                                <dd class="col-sm-7">
                                    {{ $order->paymentTransaction->metadata['payment_method'] ?? 'Flow' }}
                                </dd>

                                <dt class="col-sm-5 text-muted">Paciente:</dt>
                                <dd class="col-sm-7">{{ auth()->user()->name }}</dd>
                            </dl>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="{{ route('patient.orders') }}" class="btn btn-primary btn-lg rounded-4 fw-bold shadow-sm">
                                <i class="bi bi-file-earmark-medical me-2"></i> Ver mis órdenes
                            </a>
                            <button onclick="window.print()" class="btn btn-outline-secondary btn-lg rounded-4 fw-bold border-0 bg-light">
                                <i class="bi bi-printer me-2"></i> Imprimir Comprobante
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <p class="text-muted small">¿Necesitas ayuda? <a href="#" class="text-primary fw-bold text-decoration-none">Contacta a soporte</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
