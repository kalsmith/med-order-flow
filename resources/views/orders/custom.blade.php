@extends('layouts.app')

@section('content')
<style>
    body { background-color: #f8fafc; }
    .step-indicator { font-size: 0.8rem; font-weight: 700; text-uppercase: uppercase; letter-spacing: 1px; }
    .form-section { border-left: 3px solid #e2e8f0; transition: border-color 0.3s; }
    .form-section:focus-within { border-left-color: #0d6efd; }
    .card-custom { border: none; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.03); }
    .input-group-text { background: none; border-right: none; color: #94a3b8; }
    .form-control { border-left: none; }
    .form-control:focus { box-shadow: none; border-color: #dee2e6; }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="d-flex justify-content-between align-items-center mb-4 px-3">
                <div class="step-indicator text-primary">
                    <span class="badge rounded-pill bg-primary me-2">1</span> Solicitud
                </div>
                <div class="step-indicator text-muted opacity-50">
                    <span class="badge rounded-pill bg-secondary me-2">2</span> Perfil
                </div>
                <div class="step-indicator text-muted opacity-50">
                    <span class="badge rounded-pill bg-secondary me-2">3</span> Pago
                </div>
            </div>

            <div class="card card-custom bg-white">
                <div class="card-body p-4 p-md-5">
                    <div class="row">
                        <div class="col-md-5 border-end d-none d-md-block">
                            <h2 class="fw-bold mb-4">Solicitud Médica Especial</h2>
                            <p class="text-muted">Si no encuentras tu examen en nuestro catálogo, descríbelo aquí.</p>

                            <ul class="list-unstyled mt-5">
                                <li class="mb-3 d-flex align-items-center text-muted small">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i> Revisión por médico titulado
                                </li>
                                <li class="mb-3 d-flex align-items-center text-muted small">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i> Orden válida en todo Chile
                                </li>
                                <li class="mb-3 d-flex align-items-center text-muted small">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i> Formato PDF listo para lab
                                </li>
                            </ul>

                            <div class="mt-5 p-3 bg-light rounded-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-shield-lock-fill fs-2 text-primary me-3"></i>
                                    <div class="small text-muted">Protección de datos bajo estándares de salud.</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7 ps-md-4">
                            <form action="{{ route('profile.store') }}" method="POST">
                                @csrf

                                <div class="form-section ps-3 mb-4">
                                    <label class="form-label fw-bold text-dark">¿Qué exámenes necesitas?</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-light-subtle"><i class="bi bi-search"></i></span>
                                        <textarea name="custom_exam_name"
                                            class="form-control bg-light border-light-subtle rounded-end-4"
                                            rows="3"
                                            placeholder="Ej: Hemograma, Perfil Lipídico y TSH..."
                                            required></textarea>
                                    </div>
                                    <div class="form-text small">Indica el nombre del examen o el área (ej: cardiología).</div>
                                </div>

                                <div class="form-section ps-3 mb-4">
                                    <label class="form-label fw-bold text-dark">Motivo o Síntomas (Opcional)</label>
                                    <textarea name="symptoms"
                                        class="form-control bg-light border-light-subtle rounded-4"
                                        rows="2"
                                        placeholder="Ej: Control anual, fatiga persistente..."></textarea>
                                    <div class="form-text small">Esto ayuda al médico a validar la pertinencia del examen.</div>
                                </div>

                                <div class="form-section ps-3 mb-4">
                                    <label class="form-label fw-bold text-dark">Urgencia del requerimiento</label>
                                    <div class="d-flex gap-3">
                                        <input type="radio" class="btn-check" name="urgency" id="urg_normal" value="normal" checked>
                                        <label class="btn btn-outline-light text-dark border-light-subtle flex-fill rounded-3" for="urg_normal">Control</label>

                                        <input type="radio" class="btn-check" name="urgency" id="urg_high" value="high">
                                        <label class="btn btn-outline-light text-dark border-light-subtle flex-fill rounded-3" for="urg_high">Molestias</label>
                                    </div>
                                </div>

                                <div class="alert alert-warning border-0 rounded-4 p-3 mb-4">
                                    <div class="d-flex align-items-center">
                                        <div class="fw-bold me-2">Costo del Servicio:</div>
                                        <div class="badge bg-dark fs-6">$9.990</div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold rounded-4 shadow-sm">
                                    Siguiente: Datos del Paciente <i class="bi bi-person-vcard ms-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-center mt-4 text-muted small">
                Al hacer clic en "Siguiente", confirmas que eres mayor de 18 años.
            </p>
        </div>
    </div>
</div>
@endsection
