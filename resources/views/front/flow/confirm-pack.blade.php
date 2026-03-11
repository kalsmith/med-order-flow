<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Orden - MedOrder Flow</title>

    @livewireStyles

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root { --primary-color: #0d6efd; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8faff;
            color: #212529;
            background-image: radial-gradient(#d1d9e6 0.5px, transparent 0.5px);
            background-size: 20px 20px;
        }
        .card-confirm { border: none; border-radius: 28px; box-shadow: 0 25px 50px rgba(0,0,0,0.06); overflow: hidden; }
        .bg-gradient-blue { background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); }

        /* Ajustes para los inputs de Livewire */
        .form-control, .form-select {
            border: 1px solid #e2e8f0;
            padding: 12px 16px;
            border-radius: 12px;
            transition: all 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        }
        .transition-all { transition: all 0.3s ease; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="text-center mb-4">
                <a href="{{ route('home') }}" class="d-inline-block mb-3">
                    <img src="/logo.png" alt="Logo" height="40" class="opacity-75">
                </a>
                <h2 class="fw-bold h3 text-dark">Finalizar Compra</h2>
                <p class="text-muted">Selecciona quién se realizará el examen</p>
            </div>

            {{-- LLAMADA AL COMPONENTE LIVEWIRE --}}
            {{-- Pasamos el ID del examen que viene del controlador --}}
            @livewire('order-checkout', ['examTypeId' => $exam_type->id])

            <div class="text-center mt-5">
                <p class="text-muted small">
                    <i class="bi bi-shield-lock me-1"></i>
                    Tus datos están protegidos bajo estándares de seguridad médica.
                </p>
                <div class="d-flex justify-content-center gap-4 opacity-50 filter-grayscale">
                    <i class="bi bi- credit-card fs-4"></i>
                    <i class="bi bi-paypal fs-4"></i>
                    <i class="bi bi-wallet2 fs-4"></i>
                </div>
            </div>

        </div>
    </div>
</div>

@livewireScripts
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
