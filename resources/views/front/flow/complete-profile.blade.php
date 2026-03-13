@extends('layouts.front')

@section('title', 'Completar Perfil - MedOrder Flow')

@push('styles')
<style>
    .card-profile {
        border: none;
        border-radius: 28px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.06);
        overflow: hidden;
        background: white;
    }
    .bg-gradient-blue {
        background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);
    }
    .user-info-box {
        background: #f8fafc;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
    }

    /* Inputs más modernos */
    .form-label { font-size: 0.75rem; letter-spacing: 0.5px; margin-bottom: 0.5rem; }
    .form-control, .form-select {
        border-radius: 14px;
        padding: 12px 16px;
        border-color: #e2e8f0;
        font-size: 0.95rem;
    }
    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.08);
        border-color: #0d6efd;
    }

    .input-group-text {
        border-radius: 14px 0 0 14px;
        border: 1px solid #e2e8f0;
        border-right: none;
        background: #f8fafc;
        color: #64748b;
        font-weight: 600;
    }
    .input-group > .form-control {
        border-radius: 0 14px 14px 0;
    }

    .btn-save {
        border-radius: 18px;
        font-weight: 700;
        padding: 14px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(13, 110, 253, 0.2); }

    .badge-step {
        background: rgba(255,255,255,0.15);
        color: white;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        margin-right: 10px;
        border: 1px solid rgba(255,255,255,0.3);
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">

            <div class="card card-profile">
                {{-- Header con Identidad Visual --}}
                <div class="p-4 bg-gradient-blue text-white text-center">
                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <span class="badge-step fw-bold">1</span>
                        <span class="small text-uppercase fw-bold opacity-75" style="letter-spacing: 1px;">Datos del Paciente</span>
                    </div>
                    <h4 class="fw-800 mb-0 text-white">
                        {{ $exam_type->name ?? 'Información Personal' }}
                    </h4>
                </div>

                <div class="p-4 p-md-5">

                    {{-- Feedback de cuenta --}}
                    <div class="user-info-box p-3 mb-4 d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=0D6EFD&color=fff&rounded=true"
                                 width="45" class="shadow-sm" alt="Avatar">
                        </div>
                        <div class="ms-3">
                            <p class="text-muted mb-0 small" style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase;">Sesión activa</p>
                            <h6 class="mb-0 fw-bold text-dark">{{ auth()->user()->email }}</h6>
                        </div>
                    </div>

                    <form action="{{ route('profile.store.flow') }}" method="POST" id="profileForm">
                        @csrf
                        <input type="hidden" name="intent_type" value="{{ $type }}">
                        <input type="hidden" name="intent_id" value="{{ $id }}">

                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted text-uppercase">Nombre Completo</label>
                            <input type="text" name="full_name" class="form-control"
                                   placeholder="Ej: Juan Pérez González"
                                   value="{{ old('full_name', auth()->user()->name) }}" required>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-7 mb-3">
                                <label class="form-label fw-bold text-muted text-uppercase">RUT</label>
                                <input type="text" id="rut_input" name="rut"
                                       class="form-control @error('rut') is-invalid @enderror"
                                       placeholder="12.345.678-K"
                                       value="{{ old('rut') }}" required>
                                @error('rut') <div class="invalid-feedback small fw-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-sm-5 mb-3">
                                <label class="form-label fw-bold text-muted text-uppercase">Sexo Biológico</label>
                                <select name="gender_biologic" class="form-select" required>
                                    <option value="" selected disabled>Seleccionar</option>
                                    <option value="Masculino" {{ old('gender_biologic') == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                    <option value="Femenino" {{ old('gender_biologic') == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted text-uppercase">Fecha de Nacimiento</label>
                            <input type="date" name="birth_date" class="form-control"
                                   max="{{ date('Y-m-d') }}"
                                   value="{{ old('birth_date') }}" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted text-uppercase">Teléfono de contacto</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0">+56</span>
                                <input type="tel" name="phone" class="form-control"
                                       placeholder="9 1234 5678"
                                       value="{{ old('phone') }}" required>
                            </div>
                            <div class="form-text small">Lo usaremos solo para notificarte sobre tu orden.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-save shadow-sm mb-3">
                            Confirmar Datos <i class="bi bi-chevron-right ms-2"></i>
                        </button>
                    </form>

                    <div class="text-center">
                        <a href="{{ route('home') }}" class="text-muted small text-decoration-none hover-primary">
                            <i class="bi bi-x-circle me-1"></i> Cancelar y volver al inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Formateador de RUT mejorado
    const rutInput = document.getElementById('rut_input');

    const formatRut = (value) => {
        let clean = value.replace(/[^\dkK]/g, '');
        if (clean.length <= 1) return clean.toUpperCase();
        let dv = clean.slice(-1).toUpperCase();
        let body = clean.slice(0, -1);
        return body.replace(/\B(?=(\d{3})+(?!\d))/g, ".") + '-' + dv;
    }

    rutInput.addEventListener('input', (e) => {
        let cursor = e.target.selectionStart;
        let oldLen = e.target.value.length;
        e.target.value = formatRut(e.target.value);

        // Ajustar cursor si es necesario
        if (oldLen < e.target.value.length) cursor++;
        e.target.setSelectionRange(cursor, cursor);
    });

    // Validar edad mínima (Opcional, pero recomendado)
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        const birthDate = new Date(this.birth_date.value);
        const today = new Date();
        if (birthDate > today) {
            e.preventDefault();
            alert("La fecha de nacimiento no puede ser futura.");
        }
    });
</script>
@endpush
