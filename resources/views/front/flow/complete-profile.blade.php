@extends('layouts.front')

@section('title', 'Completar Perfil - MedOrder Flow')

@push('styles')
<style>
    body { background-color: #f8faff; }
    .card-profile { border: none; border-radius: 28px; box-shadow: 0 25px 50px rgba(0,0,0,0.06); overflow: hidden; background: white; }
    .bg-gradient-blue { background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); }
    .user-info-box { background: #f1f5f9; border-radius: 18px; border: 1px solid #e2e8f0; }

    .form-control, .form-select { border-radius: 12px; padding: 12px 15px; border-color: #edf2f7; }
    .form-control:focus { box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1); }

    .btn-save { border-radius: 16px; font-weight: 700; padding: 16px; transition: all 0.3s; }
    .badge-step {
        background: rgba(255,255,255,0.2);
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        margin-right: 8px;
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <div class="card card-profile">
                {{-- Header con progreso --}}
                <div class="p-4 bg-gradient-blue text-white text-center">
                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <span class="badge-step">1</span>
                        <span class="small text-uppercase fw-bold opacity-75">Configuración de cuenta</span>
                    </div>
                    <h3 class="fw-extrabold mb-0 text-white">{{ $exam_type->name ?? 'Nueva Orden' }}</h3>
                </div>

                <div class="p-4 p-md-5">
                    {{-- Caja de cuenta autenticada (Google/Email) --}}
                    <div class="user-info-box p-3 mb-4">
                        <label class="small text-muted fw-bold text-uppercase mb-2 d-block" style="font-size: 0.65rem;">Cuenta vinculada</label>
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=0D6EFD&color=fff&rounded=true" width="40" alt="Avatar">
                            </div>
                            <div class="ms-3 text-start">
                                <h6 class="mb-0 fw-bold">{{ auth()->user()->name }}</h6>
                                <span class="text-muted small">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('profile.store.flow') }}" method="POST">
                        @csrf
                        <input type="hidden" name="intent_type" value="{{ $type }}">
                        <input type="hidden" name="intent_id" value="{{ $id }}">

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Nombre Completo del Paciente</label>
                            <input type="text" name="full_name" class="form-control" value="{{ old('full_name', auth()->user()->name) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-7 mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase">RUT</label>
                                <input type="text" id="rut_input" name="rut" class="form-control @error('rut') is-invalid @enderror" placeholder="12.345.678-k" value="{{ old('rut') }}" required>
                                @error('rut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-5 mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase">Género</label>
                                <select name="gender_biologic" class="form-select" required>
                                    <option value="" selected disabled>...</option>
                                    <option value="Masculino" {{ old('gender_biologic') == 'Masculino' ? 'selected' : '' }}>Masc.</option>
                                    <option value="Femenino" {{ old('gender_biologic') == 'Femenino' ? 'selected' : '' }}>Fem.</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Fecha de Nacimiento</label>
                            <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date') }}" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted text-uppercase">Teléfono de contacto</label>
                            <div class="input-group">
                                <span class="input-group-text border-0 bg-light">+56</span>
                                <input type="text" name="phone" class="form-control" placeholder="9 1234 5678" value="{{ old('phone') }}" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-save shadow-sm mb-3">
                            Guardar y Continuar <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </form>

                    <div class="text-center">
                        <a href="{{ route('home') }}" class="text-muted small text-decoration-none">Cancelar proceso</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const rutInput = document.getElementById('rut_input');

    function formatRutValue(value) {
        let clean = value.replace(/[^\dkK]/g, '');
        if (clean.length <= 1) return clean.toUpperCase();
        let dv = clean.slice(-1).toUpperCase();
        let body = clean.slice(0, -1);
        return body.replace(/\B(?=(\d{3})+(?!\d))/g, ".") + '-' + dv;
    }

    rutInput.addEventListener('input', function(e) {
        e.target.value = formatRutValue(e.target.value);
    });

    window.addEventListener('DOMContentLoaded', () => {
        if (rutInput.value) {
            rutInput.value = formatRutValue(rutInput.value);
        }
    });
</script>
@endpush
