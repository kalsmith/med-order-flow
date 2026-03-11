<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Órdenes | MedOrder Flow</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root { --primary-color: #0d6efd; --bg-light: #f8faff; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); color: #212529; }
        .navbar { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); }
        .card-order { border-radius: 20px; border: 1px solid #edf2f7; transition: transform 0.2s; }
        .card-order:hover { transform: translateY(-3px); }
        .badge-status { border-radius: 10px; padding: 0.5em 1em; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; }
        .btn-action { border-radius: 12px; font-weight: 600; }
        .empty-state { padding: 4rem 2rem; text-align: center; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light sticky-top border-bottom shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-extrabold text-primary fs-3 d-flex align-items-center" href="{{ route('home') }}">
                <i class="bi bi-droplet-fill me-2"></i>
                <span style="letter-spacing: -1px;">MedOrder<span class="text-dark">Flow</span></span>
            </a>
            <div class="ms-auto d-flex align-items-center">
                <span class="text-muted small d-none d-md-inline me-3">Hola, {{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link text-danger p-0 border-0"><i class="bi bi-box-arrow-right fs-4"></i></button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <h2 class="fw-extrabold mb-0" style="letter-spacing: -1px;">Mis Órdenes Médicas</h2>
                <p class="text-muted">Gestiona tus solicitudes y descarga tus órdenes firmadas.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="{{ route('home') }}" class="btn btn-primary btn-action shadow-sm px-4">
                    <i class="bi bi-plus-lg me-2"></i> Nueva Solicitud
                </a>
            </div>
        </div>

        @if($orders->isEmpty())
            <div class="card card-order border-0 shadow-sm empty-state">
                <div class="mb-4 text-muted"><i class="bi bi-file-earmark-medical display-1 opacity-25"></i></div>
                <h4>Aún no tienes órdenes</h4>
                <p class="text-muted">Tus órdenes aparecerán aquí una vez que realices una solicitud.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach($orders as $order)
                <div class="col-12">
                    <div class="card card-order border-0 shadow-sm overflow-hidden">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-5 mb-3 mb-md-0">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-light text-primary border me-2" style="font-size: 0.7rem;">ID: {{ substr($order->id, 0, 8) }}</span>
                                        <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i> {{ $order->created_at->format('d/m/Y H:i') }}</span>
                                    </div>

                                    <h5 class="fw-bold mb-1 text-dark">
                                        @if($order->type === 'custom')
                                            Solicitud Especial
                                        @else
                                            {{ $order->examType->name ?? 'Examen General' }}
                                        @endif
                                    </h5>

                                    <p class="text-muted small mb-0">
                                        <i class="bi bi-hospital me-1"></i>
                                        @if($order->type === 'custom')
                                            Revisión por Especialista
                                        @else
                                            {{ $order->examType->specialty->name ?? 'Especialidad Médica' }}
                                        @endif
                                    </p>
                                </div>

                                <div class="col-md-3 text-md-center mb-3 mb-md-0">
                                    <div class="mb-1">
                                        @switch($order->status)
                                            @case('pending')
                                                <span class="badge badge-status bg-warning-subtle text-warning border border-warning-subtle">Pendiente de Pago</span>
                                                @break
                                            @case('paid')
                                                <span class="badge badge-status bg-info-subtle text-info border border-info-subtle">En Revisión</span>
                                                @break
                                            @case('signed')
                                                <span class="badge badge-status bg-success-subtle text-success border border-success-subtle">Lista para Descarga</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge badge-status bg-secondary-subtle text-secondary border border-secondary-subtle">Anulada</span>
                                                @break
                                            @default
                                                <span class="badge badge-status bg-danger-subtle text-danger border border-danger-subtle">{{ ucfirst($order->status) }}</span>
                                        @endswitch
                                    </div>
                                    <div class="fw-bold text-dark fs-5">$ {{ number_format($order->amount, 0, ',', '.') }}</div>
                                </div>

                                <div class="col-md-4 text-md-end">
                                    <div class="d-flex gap-2 justify-content-md-end">
                                        @if($order->status === 'pending')
                                            <a href="{{ route('checkout.index', $order->id) }}" class="btn btn-primary btn-action flex-grow-1 shadow-sm">
                                                <i class="bi bi-credit-card me-2"></i> Pagar
                                            </a>
                                        @elseif($order->status === 'signed')
                                            <a href="{{ route('orders.download', $order->id) }}" class="btn btn-success btn-action flex-grow-1 shadow-sm">
                                                <i class="bi bi-file-earmark-arrow-down-fill me-2"></i> Descargar
                                            </a>
                                        @endif

                                        @if($order->status === 'rejected' && $order->rejection_reason)
                                            <button class="btn btn-outline-danger btn-action"
                                                onclick="alert('Motivo de rechazo: {{ $order->rejection_reason }}')"
                                                title="Ver motivo">
                                                <i class="bi bi-info-circle"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-5 d-flex justify-content-center">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
