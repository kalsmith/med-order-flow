<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud Especial - MedOrder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .step-indicator { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .card-custom { border: none; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.04); }
        .form-section { border-left: 3px solid #e2e8f0; padding-left: 1.5rem; margin-bottom: 2rem; }
        .form-section:focus-within { border-left-color: #0d6efd; }
        .btn-gradient { background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); border: none; color: white; }
        .bg-medical { background-color: #eef2ff; color: #4338ca; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">

            <div class="d-flex justify-content-between mb-4 px-3">
                <div class="step-indicator text-primary"><i class="bi bi-1-circle-fill"></i> Solicitud</div>
                <div class="step-indicator text-muted opacity-50"><i class="bi bi-2-circle"></i> Datos</div>
                <div class="step-indicator text-muted opacity-50"><i class="bi bi-3-circle"></i> Pago</div>
            </div>

            <div class="card card-custom">
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-md-4 bg-medical p-4 p-md-5 rounded-start-4 d-none d-md-block">
                            <h3 class="fw-bold h5 mb-4">Análisis Farmacéutico</h3>
                            <p class="small opacity-75">Tu solicitud será revisada por un Químico Farmacéutico para asegurar la pertinencia técnica antes de la firma médica.</p>

                            <div class="mt-4 pt-4 border-top border-primary-subtle">
                                <div class="d-flex mb-3 small">
                                    <i class="bi bi-shield-check me-2"></i> Firma Electrónica Avanzada
                                </div>
                                <div class="d-flex mb-3 small">
                                    <i class="bi bi-clock-history me-2"></i> Entrega en < 24hrs
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8 p-4 p-md-5 bg-white rounded-end-4">
                            <h2 class="fw-bold h4 mb-4 text-dark">¿Qué examen necesitas?</h2>

                            <form action="{{ route('profile.store') }}" method="POST">
                                @csrf

                                <div class="form-section">
                                    <label class="form-label fw-bold small text-muted text-uppercase">Detalle de Exámenes</label>
                                    <textarea name="custom_exam_name"
                                        class="form-control bg-light border-0 p-3"
                                        rows="3"
                                        placeholder="Ej: Hemograma completo, TGO, TGP y Creatinina..."
                                        required></textarea>
                                    <div class="form-text small">Escribe los nombres tal como aparecen en tu indicación o receta previa.</div>
                                </div>

                                <div class="form-section">
                                    <label class="form-label fw-bold small text-muted text-uppercase">Contexto Clínico (Para el QF)</label>
                                    <textarea name="symptoms"
                                        class="form-control bg-light border-0 p-3"
                                        rows="2"
                                        placeholder="Ej: Control de tiroides por hipotiroidismo diagnosticado..."></textarea>
                                    <div class="form-text small">Información relevante para que el profesional valide la orden.</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-bold small text-muted text-uppercase">Tipo de Paciente</label>
                                        <select name="patient_type" class="form-select bg-light border-0">
                                            <option value="adulto">Adulto</option>
                                            <option value="pediatrico">Pediátrico</option>
                                            <option value="geriatrico">Geriátrico</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-bold small text-muted text-uppercase">Urgencia</label>
                                        <select name="urgency" class="form-select bg-light border-0">
                                            <option value="normal">Control de Rutina</option>
                                            <option value="seguimiento">Seguimiento Patología</option>
                                            <option value="urgente">Molestias Agudas</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="alert bg-light border-0 d-flex align-items-center rounded-4 p-3 mb-4">
                                    <i class="bi bi-tag-fill me-3 fs-4 text-primary"></i>
                                    <div>
                                        <div class="small text-muted">Precio único revisión manual</div>
                                        <div class="fw-bold fs-5">$9.990</div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-gradient btn-lg w-100 py-3 fw-bold rounded-4 shadow-sm">
                                    Continuar a mis datos <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-center mt-4 text-muted small">
                Sesión: <strong>{{ auth()->user()->email }}</strong> |
                <i class="bi bi-lock-fill text-success"></i> Conexión Segura
            </p>
        </div>
    </div>
</div>
</body>
</html>
