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
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8faff;
            color: #212529;
            background-image: radial-gradient(#d1d9e6 0.5px, transparent 0.5px);
            background-size: 20px 20px;
        }
        .card-custom { border: none; border-radius: 28px; box-shadow: 0 25px 50px rgba(0,0,0,0.06); overflow: hidden; background: white; }
        .bg-gradient-blue { background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); }
        .transition-all { transition: all 0.3s ease; }

        /* Estilos para los inputs dentro del componente */
        .form-control, .form-select { border-radius: 12px; padding: 12px 15px; border-color: #edf2f7; }
        .form-control:focus { box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1); border-color: var(--primary-color); }

        .patient-card { cursor: pointer; border-radius: 20px; border: 2px solid #f1f5f9; transition: all 0.2s; }
    </style>
</head>
<body>

<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">

            {{-- Navegación superior --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ route('home') }}" class="btn btn-link text-decoration-none fw-bold p-0 text-dark">
                    <i class="bi bi-chevron-left"></i> Volver
                </a>
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">Flujo Personalizado</span>
            </div>

            <div class="card-custom">
                {{-- Encabezado Visual --}}
                <div class="bg-gradient-blue p-4 text-white text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-white bg-opacity-20 rounded-circle mb-3" style="width: 56px; height: 56px;">
                        <i class="bi bi-chat-quote fs-3"></i>
                    </div>
                    <h2 class="fw-bold h4 mb-1">Solicitud a Medida</h2>
                    <p class="text-white text-opacity-75 small mb-0">Un médico revisará tu requerimiento manualmente</p>
                </div>

                <div class="card-body p-4">
                    {{--
                        REUTILIZACIÓN: Llamamos al componente OrderCheckout
                        pero pasamos null en examTypeId para activar el modo "Custom"
                    --}}
                    @livewire('order-checkout', ['examTypeId' => null])
                </div>
            </div>

            {{-- Seguridad --}}
            <div class="text-center mt-4">
                <p class="small text-muted mb-0">
                    <i class="bi bi-lock-fill me-1 text-primary"></i>
                    Solicitud segura y encriptada bajo estándares HIPAA
                </p>
            </div>

        </div>
    </div>
</div>

@livewireScripts
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
