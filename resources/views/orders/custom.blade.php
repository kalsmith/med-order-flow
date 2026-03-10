<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud Especial - MedOrder Flow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7fe; font-family: 'Inter', sans-serif; }
        .card-custom { border: none; border-radius: 24px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); }
        .btn-gradient { background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); border: none; color: white; transition: all 0.3s; }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(13, 110, 253, 0.2); color: white; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="text-center mb-4">
                <a href="/" class="text-decoration-none fw-bold fs-4 text-primary">
                    <i class="bi bi-droplet-fill"></i> MedOrder Flow
                </a>
            </div>

            <div class="card card-custom p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <div class="bg-primary-subtle text-primary d-inline-block p-3 rounded-circle mb-3">
                        <i class="bi bi-magic fs-3"></i>
                    </div>
                    <h2 class="fw-bold h4">Solicitud de Examen Especial</h2>
                    <p class="text-muted small">Nuestro equipo médico revisará tu requerimiento para emitir la orden correcta.</p>
                </div>

                <form action="{{ route('profile.store') }}" method="POST">
                    @csrf
                    {{-- Si no tiene perfil, lo mandamos a completar perfil primero con los datos del examen --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted">¿Qué examen necesitas?</label>
                        <textarea name="custom_exam_name" class="form-control rounded-4 border-light-subtle bg-light p-3" rows="3"
                            placeholder="Ej: Necesito un Perfil Bioquímico y de Orina completo..." required></textarea>
                    </div>

                    <div class="alert alert-info border-0 rounded-4 small d-flex align-items-center">
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                        <div>
                            Al continuar, te pediremos tus datos básicos para la firma legal de la orden médica.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gradient btn-lg w-100 py-3 fw-bold rounded-4 shadow-sm">
                        Continuar con mis datos <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </form>
            </div>

            <p class="text-center mt-4 text-muted small">
                <i class="bi bi-shield-check text-success me-1"></i> Sesión iniciada como {{ auth()->user()->email }}
            </p>
        </div>
    </div>
</div>
</body>
</html>
