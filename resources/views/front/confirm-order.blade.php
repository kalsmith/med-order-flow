<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión Médica - MedOrder Flow</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    @livewireStyles

    <style>
        body { background-color: #f4f7fe; font-family: 'Inter', sans-serif; color: #334155; }

        /* Contenedor más amplio para el diseño de dos columnas */
        .main-container { max-width: 1000px; margin: 0 auto; }

        .bg-medical { background: #f8faff; border-right: 1px solid #e2e8f0; }
        .card-custom { border: none; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.05); }

        /* Estilos para las tarjetas de paciente del componente */
        .patient-card {
            border: 2px solid #f1f5f9;
            border-radius: 16px;
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
            height: 100%;
        }
        .patient-card:hover { border-color: #0d6efd; background: #f8faff; }
        .patient-card.border-primary { border-color: #0d6efd !important; background: #eff6ff; }
        .patient-card.border-dashed { border-style: dashed !important; border-color: #cbd5e1; }

        .patient-avatar {
            width: 35px;
            height: 35px;
            background: #f1f5f9;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #94a3b8;
        }

        .btn-gradient {
            background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);
            color: white;
            border: none;
        }
        .btn-gradient:hover { color: white; opacity: 0.9; transform: translateY(-1px); }

        .step-indicator { font-size: 0.85rem; font-weight: 600; }
    </style>
</head>
<body>

<div class="container py-4 py-md-5">
    <div class="text-center mb-4">
        <a href="/" class="text-decoration-none fw-bold fs-4 text-primary">
            <i class="bi bi-droplet-fill"></i> MedOrder Flow
        </a>
    </div>

    <div class="main-container">
        {{-- Aquí vive el diseño de dos columnas --}}
        @livewire('order-checkout', ['examId' => $exam->id])
    </div>

    <div class="text-center mt-4">
        <a href="{{ url()->previous() }}" class="text-muted small text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Volver atrás
        </a>
    </div>
</div>

@livewireScripts
</body>
</html>
