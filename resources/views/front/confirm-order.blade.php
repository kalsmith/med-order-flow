<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Pedido - MedOrder Flow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f4f7fe; font-family: 'Inter', sans-serif; color: #334155; }
        .card-confirm { border: none; border-radius: 24px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); background: white; }
        .summary-item { border-bottom: 1px solid #f1f5f9; padding: 16px 0; }
        .summary-item:last-child { border-bottom: none; }
        .bg-primary-subtle { background-color: #eff6ff !important; border: 1px solid #dbeafe; }
        .badge-step { font-size: 0.7rem; letter-spacing: 1px; }
        .btn-checkout { transition: all 0.3s ease; }
        .btn-checkout:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(13, 110, 253, 0.2); }

        /* Nueva alerta de términos */
        .alert-no-refund {
            background-color: #fffbeb;
            border: 1px solid #fef3c7;
            border-radius: 16px;
            padding: 15px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="text-center mb-4">
                <a href="/" class="text-decoration-none fw-bold fs-4 text-primary">
                    <i class="bi bi-droplet-fill"></i> MedOrder Flow
                </a>
            </div>

            <div class="card card-confirm mb-4">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle badge-step text-uppercase px-3 py-2 rounded-pill mb-2">Paso 2 de 2</span>
                        <h2 class="fw-bold h3">Resumen de tu Orden</h2>
                    </div>

                    {{-- Info del Paciente --}}
                    <div class="bg-light p-3 rounded-4 mb-4 border-start border-primary border-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-primary fw-bold small text-uppercase mb-2" style="font-size: 0.7rem;">Paciente Solicitante</h6>
                                <p class="mb-0 fw-bold text-dark fs-5">{{ $patient->full_name }}</p>
                                <p class="mb-0 text-muted small">RUT: {{ $patient->rut }}</p>
                            </div>
                            <a href="{{ route('profile.complete') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="bi bi-pencil me-1"></i> Editar
                            </a>
                        </div>
                    </div>

                    <h6 class="text-muted fw-bold small text-uppercase mb-3" style="font-size: 0.7rem;">Detalle del Examen</h6>

                    <div class="summary-item d-flex justify-content-between align-items-center">
                        <div class="pe-3">
                            <span class="d-block fw-semibold text-dark">{{ $exam->name }}</span>
                            @if($exam->description)
                                <span class="text-muted small d-block mt-1">
                                    <i class="bi bi-info-circle me-1"></i> {{ $exam->description }}
                                </span>
                            @endif
                        </div>
                        <span class="fw-bold text-dark text-nowrap">${{ number_format($exam->base_price, 0, ',', '.') }}</span>
                    </div>

                    {{-- Total --}}
                    <div class="summary-item d-flex justify-content-between mt-3 bg-primary-subtle p-3 rounded-4">
                        <span class="fw-bold text-primary fs-5">Total a Pagar</span>
                        <span class="fw-bold text-primary fs-5">${{ number_format($exam->base_price, 0, ',', '.') }}</span>
                    </div>

                    {{-- Advertencia de No Reembolso --}}
                    <div class="alert-no-refund mt-4 d-flex align-items-start">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-3 fs-4"></i>
                        <div class="small">
                            <strong class="text-dark d-block mb-1">Aviso de Producto Digital</strong>
                            Este es un servicio de <strong>emisión inmediata</strong>. Una vez realizado el pago, no se aceptan devoluciones ni reembolsos.
                        </div>
                    </div>

                    {{-- Formulario de Envío a Flow --}}
                    <form action="{{ route('orders.store.public') }}" method="POST" class="mt-4" id="confirmForm">
                        @csrf
                        <input type="hidden" name="exam_type_id" value="{{ $exam->id }}">
                        <input type="hidden" name="amount" value="{{ $exam->base_price }}">

                        <div class="form-check mb-4 bg-light p-3 rounded-3 border">
                            <input class="form-check-input ms-0 me-2" type="checkbox" id="terms" required>
                            <label class="form-check-label small text-muted lh-sm" for="terms">
                                Confirmo que los <strong>datos del paciente son correctos</strong>, he verificado el examen seleccionado y acepto que no habrá reembolsos tras el pago.
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm py-3 fw-bold rounded-4 btn-checkout">
                            Confirmar y Pagar <i class="bi bi-credit-card-2-back-fill ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-center text-muted small px-3">
                <i class="bi bi-shield-lock-fill me-2 fs-4 text-success"></i>
                <div class="lh-sm">
                    <span class="d-block fw-bold text-dark">Pago 100% Seguro</span>
                    <span>Procesado por Flow bajo estándares de seguridad bancaria.</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('confirmForm').onsubmit = function() {
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Redirigiendo a Flow...';
    };
</script>

</body>
</html>
