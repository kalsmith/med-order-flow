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

        /* Estilos para las tarjetas de paciente */
        .patient-card { transition: all 0.2s ease; border-radius: 20px !important; }
        .patient-card:hover { transform: translateY(-3px); }
        .border-dashed { border-style: dashed !important; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">

            <div class="d-flex justify-content-between mb-4 px-3">
                <div class="step-indicator text-primary"><i class="bi bi-1-circle-fill"></i> Solicitud</div>
                <div class="step-indicator text-muted opacity-50"><i class="bi bi-2-circle"></i> Perfil</div>
                <div class="step-indicator text-muted opacity-50"><i class="bi bi-3-circle"></i> Pago</div>
            </div>

            <div class="card card-custom">
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-md-4 bg-medical p-4 p-md-5 rounded-start-4 d-none d-lg-block">
                            <h3 class="fw-bold h5 mb-4">Revisión Profesional</h3>
                            <p class="small opacity-75">Tu solicitud será evaluada para asegurar que la orden médica sea emitida con precisión técnica.</p>

                            <div class="mt-4 pt-4 border-top border-primary-subtle">
                                <div class="d-flex mb-3 small align-items-center">
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

                        <div class="col-md-12 col-lg-8 p-4 p-md-5 bg-white rounded-end-4">
                            <h2 class="fw-bold h4 mb-4 text-dark">Detalles de la Solicitud</h2>

                            <form action="{{ route('orders.store.custom') }}" method="POST">
                                @csrf

                                {{-- Selector de Pacientes (Estilo Perfiles) --}}
                                <div class="form-section">
                                    <label class="form-label fw-bold small text-muted text-uppercase mb-3">
                                        <i class="bi bi-people-fill me-1"></i> ¿Para quién es el examen?
                                    </label>

                                    <input type="hidden" name="patient_id" id="selected_patient_id" value="{{ $patients->where('relationship', 'self')->first()->id ?? '' }}" required>

                                    <div class="row g-3 mb-2">
                                        @foreach($patients as $p)
                                            <div class="col-6 col-sm-4">
                                                <div onclick="selectPatient(this, {{ $p->id }})"
                                                     class="card h-100 border-2 transition-all shadow-sm patient-card {{ $p->relationship == 'self' ? 'border-primary bg-primary-subtle' : 'border-light bg-white' }}"
                                                     style="cursor: pointer;">
                                                    <div class="card-body p-3 text-center">
                                                        <div class="mb-2">
                                                            <i class="bi bi-person-circle fs-2 {{ $p->relationship == 'self' ? 'text-primary' : 'text-muted' }}"></i>
                                                        </div>
                                                        <p class="mb-1 fw-bold small text-truncate text-dark">{{ $p->full_name }}</p>
                                                        <span class="badge rounded-pill {{ $p->relationship == 'self' ? 'bg-primary' : 'bg-light text-muted border' }}" style="font-size: 0.65rem;">
                                                            {{ $p->relationship == 'self' ? 'Tú' : __($p->relationship) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        {{-- Botón Agregar Familiar --}}
                                        <div class="col-6 col-sm-4">
                                            <a href="{{ route('profile.complete') }}" class="text-decoration-none h-100">
                                                <div class="card h-100 border-2 border-dashed border-muted bg-white transition-all patient-card"
                                                     style="cursor: pointer;">
                                                    <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                                                        <i class="bi bi-person-plus fs-2 text-muted mb-1"></i>
                                                        <p class="mb-0 fw-bold small text-muted">Otro familiar</p>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="form-text small mt-2">Selecciona un perfil existente o agrega uno nuevo.</div>
                                </div>

                                <div class="form-section">
                                    <label class="form-label fw-bold small text-muted text-uppercase">Exámenes requeridos</label>
                                    <textarea name="custom_exam_name"
                                        class="form-control bg-light border-0 p-3"
                                        rows="3"
                                        placeholder="Ej: Hemograma completo, Perfil Lipídico..."
                                        required></textarea>
                                </div>

                                <div class="form-section">
                                    <label class="form-label fw-bold small text-muted text-uppercase">Síntomas o Motivo</label>
                                    <textarea name="symptoms"
                                        class="form-control bg-light border-0 p-3"
                                        rows="2"
                                        placeholder="Ej: Control de rutina, fatiga constante..."></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-bold small text-muted text-uppercase">Rango de Edad</label>
                                        <select name="patient_type" class="form-select bg-light border-0">
                                            <option value="adulto">Adulto (18+ años)</option>
                                            <option value="pediatrico">Pediátrico</option>
                                            <option value="geriatrico">Adulto Mayor</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-bold small text-muted text-uppercase">Urgencia</label>
                                        <select name="urgency" class="form-select bg-light border-0">
                                            <option value="normal">Preventivo / Rutina</option>
                                            <option value="seguimiento">Seguimiento</option>
                                            <option value="urgente">Molestias actuales</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="alert bg-light border-0 d-flex align-items-center rounded-4 p-3 mb-4">
                                    <i class="bi bi-tag-fill me-3 fs-4 text-primary"></i>
                                    <div>
                                        <div class="small text-muted">Costo total del servicio</div>
                                        <div class="fw-bold fs-5">$9.990</div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-gradient btn-lg w-100 py-3 fw-bold rounded-4 shadow-sm">
                                    Continuar al Pago <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </form>
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

<script>
function selectPatient(element, id) {
    // 1. Actualizar el input hidden
    document.getElementById('selected_patient_id').value = id;

    // 2. Limpiar estilos de todas las tarjetas de paciente
    document.querySelectorAll('.patient-card').forEach(card => {
        card.classList.remove('border-primary', 'bg-primary-subtle');
        card.classList.add('border-light', 'bg-white');

        // Resetear iconos y badges dentro de las tarjetas
        const icon = card.querySelector('.bi-person-circle');
        if(icon) icon.classList.replace('text-primary', 'text-muted');

        const badge = card.querySelector('.badge');
        if(badge) {
            badge.classList.replace('bg-primary', 'bg-light');
            badge.classList.add('text-muted', 'border');
        }
    });

    // 3. Aplicar estilos a la tarjeta seleccionada
    element.classList.remove('border-light', 'bg-white');
    element.classList.add('border-primary', 'bg-primary-subtle');

    const activeIcon = element.querySelector('.bi-person-circle');
    if(activeIcon) activeIcon.classList.replace('text-muted', 'text-primary');

    const activeBadge = element.querySelector('.badge');
    if(activeBadge) {
        activeBadge.classList.remove('bg-light', 'text-muted', 'border');
        activeBadge.classList.add('bg-primary');
    }
}
</script>
</body>
</html>
