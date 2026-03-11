<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud Especial - MedOrder</title>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    @livewireStyles
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .step-indicator { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .card-custom { border: none; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.04); overflow: hidden; }
        .form-section { border-left: 3px solid #e2e8f0; padding-left: 1.5rem; margin-bottom: 2rem; transition: border-color 0.3s; }
        .form-section:focus-within { border-left-color: #0d6efd; }
        .btn-gradient { background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); border: none; color: white; transition: all 0.3s; }
        .btn-gradient:hover { opacity: 0.9; transform: translateY(-2px); color: white; }
        .bg-medical { background-color: #eef2ff; color: #4338ca; }
        .patient-card { transition: all 0.2s ease; border-radius: 20px !important; }
        .patient-card:hover { transform: translateY(-3px); }
        .border-dashed { border-style: dashed !important; }

        /* Ajuste para inputs en modo Livewire */
        input:focus, select:focus, textarea:focus {
            box-shadow: 0 0 0 0.25 margin-bottom: 2rem;rem rgba(13, 110, 253, 0.1) !important;
            background-color: #fff !important;
        }
        .tiny { font-size: 0.75rem; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">

            {{-- Stepper --}}
            <div class="d-flex justify-content-between mb-4 px-3">
                <div class="step-indicator text-primary"><i class="bi bi-1-circle-fill"></i> Solicitud</div>
                <div class="step-indicator text-muted opacity-50"><i class="bi bi-2-circle"></i> Perfil</div>
                <div class="step-indicator text-muted opacity-50"><i class="bi bi-3-circle"></i> Pago</div>
            </div>

            <div class="card card-custom">
                <div class="card-body p-0">
                    <div class="row g-0">
                        {{-- Sidebar Informativo --}}
                        <div class="col-md-4 bg-medical p-4 p-md-5 d-none d-lg-block">
                            <h3 class="fw-bold h5 mb-4">Revisión Profesional</h3>
                            <p class="small opacity-75">Tu solicitud será evaluada para asegurar que la orden médica sea emitida con precisión técnica.</p>

                            <div class="mt-4 pt-4 border-top border-primary-subtle">
                                <div class="d-flex mb-3 small align-items-center text-primary">
                                    <i class="bi bi-shield-check me-2"></i> Firma Médica Legal
                                </div>
                                <div class="d-flex mb-3 small align-items-center">
                                    <i class="bi bi-clock-history me-2"></i> Entrega en < 24hrs
                                </div>
                                <div class="d-flex mb-3 small align-items-center">
                                    <i class="bi bi-file-earmark-pdf me-2"></i> Formato Digital PDF
                                </div>
                            </div>
                        </div>

                        {{-- Formulario Dinámico con Livewire --}}
                        <div class="col-md-12 col-lg-8 p-4 p-md-5 bg-white">
                            <h2 class="fw-bold h4 mb-4 text-dark">Detalles de la Solicitud</h2>

                            @livewire('custom-order-form')
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-center mt-4 text-muted small">
                Conectado como: <strong>{{ auth()->user()->email }}</strong>
            </p>
        </div>
    </div>
</div>

@livewireScripts
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
