<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden Personalizada - MedOrder Flow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    @livewireStyles
    <style>
        :root { --primary-color: #0d6efd; --soft-bg: #f8faff; }
        body { font-family: 'Inter', sans-serif; background-color: #f8faff; color: #212529; }
        .card-custom { border: none; border-radius: 28px; box-shadow: 0 25px 50px rgba(0,0,0,0.06); overflow: hidden; background: white; }
        .bg-gradient-blue { background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); }
        .transition-all { transition: all 0.3s ease; }

        /* Estilos consistentes con tu formulario de perfil */
        .form-control, .form-select { border-radius: 12px; padding: 12px 15px; border-color: #edf2f7; }
        .form-control:focus { box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1); }
        .btn-send { border-radius: 16px; font-weight: 700; padding: 16px; transition: all 0.3s; }

        /* Estilos para el grid de pacientes */
        .patient-card { cursor: pointer; border-radius: 20px; border: 2px solid #f1f5f9; transition: all 0.2s; }
        .patient-card.active { border-color: var(--primary-color); background-color: #eff6ff; }
    </style>
</head>
<body>

<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            {{-- Encabezado --}}
            <div class="text-center mb-4">
                <a href="{{ route('home') }}" class="text-decoration-none small fw-bold text-primary mb-3 d-inline-block">
                    <i class="bi bi-arrow-left me-1"></i> Volver al inicio
                </a>
                <h2 class="fw-bold h3 mb-1">Orden Personalizada</h2>
                <p class="text-muted">Dinos qué necesitas y un médico revisará tu solicitud.</p>
            </div>

            <div class="card-custom">
                {{-- Banner Superior --}}
                <div class="bg-gradient-blue p-4 text-white text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-white bg-opacity-20 rounded-circle mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-magic fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-0 text-white">Nueva Solicitud</h5>
                </div>

                <div class="card-body p-4 p-md-5">
                    {{-- Aquí inyectaremos el componente --}}
                    @livewire('custom-order-flow', ['patient' => $patient])
                </div>
            </div>

            {{-- Footer info --}}
            <div class="text-center mt-4">
                <p class="small text-muted">
                    <i class="bi bi-shield-check me-1 text-success"></i>
                    Tus datos médicos están protegidos con encriptación de grado militar.
                </p>
            </div>

        </div>
    </div>
</div>

@livewireScripts
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
