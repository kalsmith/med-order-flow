<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Pedido - MedOrder Flow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    {{-- Directivas de Livewire --}}
    @livewireStyles

    <style>
        body { background-color: #f4f7fe; font-family: 'Inter', sans-serif; color: #334155; }
        .card-confirm { border: none; border-radius: 24px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); background: white; }
        .summary-item { border-bottom: 1px solid #f1f5f9; padding: 16px 0; }
        .summary-item:last-child { border-bottom: none; }
        .bg-primary-subtle { background-color: #eff6ff !important; border: 1px solid #dbeafe; }
        .badge-step { font-size: 0.7rem; letter-spacing: 1px; }
        .btn-checkout { transition: all 0.3s ease; }
        .btn-checkout:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(13, 110, 253, 0.2); }

        .alert-no-refund {
            background-color: #fffbeb;
            border: 1px solid #fef3c7;
            border-radius: 16px;
            padding: 15px;
        }

        /* Estilo para las tarjetas de perfil en el componente */
        .transition-all { transition: all 0.2s ease-in-out; }
        .cursor-pointer { cursor: pointer; }
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

            {{-- LLAMADA AL COMPONENTE LIVEWIRE --}}
            {{-- Le pasamos el ID del examen que viene desde el controlador --}}
            @livewire('order-checkout', ['examId' => $exam->id])

            <div class="d-flex align-items-center justify-content-center text-muted small px-3 mt-4">
                <i class="bi bi-shield-lock-fill me-2 fs-4 text-success"></i>
                <div class="lh-sm">
                    <span class="d-block fw-bold text-dark">Pago 100% Seguro</span>
                    <span>Procesado por Flow bajo estándares de seguridad bancaria.</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Scripts de Livewire --}}
@livewireScripts

<script>
    // Este script detecta el envío del formulario dentro del componente Livewire
    document.addEventListener('submit', function(e) {
        const btn = e.target.querySelector('.btn-checkout');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Redirigiendo a Flow...';
        }
    });
</script>

</body>
</html>
