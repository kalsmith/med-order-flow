<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Pedido - MedOrder Flow</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    @livewireStyles

    <style>
        body { background-color: #f4f7fe; font-family: 'Inter', sans-serif; color: #334155; }

        /* Contenedores Principales */
        .card-confirm { border: none; border-radius: 24px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); background: white; }
        .summary-item { border-bottom: 1px solid #f1f5f9; padding: 16px 0; }
        .summary-item:last-child { border-bottom: none; }

        /* Utilidades de Diseño */
        .bg-primary-subtle { background-color: #eff6ff !important; border: 1px solid #dbeafe; }
        .badge-step { font-size: 0.7rem; letter-spacing: 1px; }
        .transition-all { transition: all 0.2s ease-in-out; }
        .cursor-pointer { cursor: pointer; }

        /* Botones y Hover Effects */
        .btn-checkout { transition: all 0.3s ease; border-radius: 15px; }
        .btn-checkout:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(13, 110, 253, 0.2); }

        /* Estilo específico para el botón "Agregar Familiar" del componente */
        .border-dashed { border-style: dashed !important; }

        /* Alerta de No Reembolso */
        .alert-no-refund {
            background-color: #fffbeb;
            border: 1px solid #fef3c7;
            border-radius: 16px;
            padding: 15px;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .container { padding-top: 2rem; }
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-7">

            <div class="text-center mb-4">
                <a href="/" class="text-decoration-none fw-bold fs-4 text-primary">
                    <i class="bi bi-droplet-fill"></i> MedOrder Flow
                </a>
            </div>

            @livewire('order-checkout', ['examId' => $exam->id])

            <div class="d-flex align-items-center justify-content-center text-muted small px-3 mt-4">
                <div class="d-flex align-items-center bg-white px-3 py-2 rounded-pill shadow-sm">
                    <i class="bi bi-shield-lock-fill me-2 text-success fs-5"></i>
                    <div class="lh-1">
                        <span class="d-block fw-bold text-dark" style="font-size: 0.75rem;">Pago 100% Seguro</span>
                        <span style="font-size: 0.65rem;">Procesado por Flow (Estándar PCI-DSS)</span>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="{{ url()->previous() }}" class="text-muted small text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i> Volver atrás
                </a>
            </div>

        </div>
    </div>
</div>

@livewireScripts

<script>
    /**
     * Feedback visual al procesar el pago.
     * Detecta el envío del formulario final para evitar múltiples clics
     * y dar feedback de redirección a la pasarela.
     */
    document.addEventListener('submit', function(e) {
        // Buscamos el botón dentro del formulario que se está enviando
        const btn = e.target.querySelector('button[type="submit"]');

        if (btn && btn.classList.contains('btn-checkout')) {
            // Evitar doble envío
            btn.disabled = true;

            // Mostrar spinner
            btn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Conectando con Flow...
            `;
        }
    });
</script>

</body>
</html>
