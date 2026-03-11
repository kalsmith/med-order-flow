@extends('layouts.admin')

@section('header', 'Registrar Nuevo Profesional')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">

        {{-- Alertas de Error --}}
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li><i class="bi bi-exclamation-circle me-2"></i>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm border-0 overflow-hidden">
            {{-- Indicador visual superior --}}
            <div class="bg-success py-1"></div>

            <div class="card-body p-4 p-md-5">
                {{-- Ajuste de ruta: admin.admin.doctors.store --}}
                <form action="{{ route('admin.admin.doctors.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-4">
                        {{-- Sección: Identidad --}}
                        <div class="col-12">
                            <h6 class="text-success text-uppercase fw-bold small mb-3">
                                <i class="bi bi-person-plus me-2"></i>Datos de Cuenta e Identidad
                            </h6>
                            <hr class="mt-0 opacity-10">
                        </div>

                        <div class="col-md-7">
                            <label class="form-label fw-bold small text-muted">Nombre Completo</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="name" class="form-control border-start-0 ps-0 @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required placeholder="Ej: Dr. Alejandro Silva">
                            </div>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-bold small text-muted">RUT</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-card-text text-muted"></i></span>
                                <input type="text" name="rut" id="rut" class="form-control border-start-0 ps-0 @error('rut') is-invalid @enderror"
                                       placeholder="12.345.678-9" value="{{ old('rut') }}" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">Email Institucional</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" required placeholder="correo@institucion.cl">
                            </div>
                            <small class="text-muted" style="font-size: 0.75rem;">
                                * Se utilizará para el inicio de sesión. La contraseña inicial será el RUT sin puntos ni guion.
                            </small>
                        </div>

                        {{-- Sección: Profesional --}}
                        <div class="col-12 mt-5">
                            <h6 class="text-success text-uppercase fw-bold small mb-3">
                                <i class="bi bi-hospital me-2"></i>Información Profesional
                            </h6>
                            <hr class="mt-0 opacity-10">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">N° Registro SIS (RNPI)</label>
                            <input type="text" name="rnpi_number" class="form-control @error('rnpi_number') is-invalid @enderror"
                                   value="{{ old('rnpi_number') }}" placeholder="Ej: 123456">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Firma Digital (Sello)</label>
                            <input type="file" name="signature" class="form-control @error('signature') is-invalid @enderror" accept="image/*">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">Especialidades</label>
                            <div class="p-3 border rounded bg-light">
                                <div class="row">
                                    @foreach($specialties as $specialty)
                                        <div class="col-md-4 col-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="specialties[]"
                                                       value="{{ $specialty->id }}" id="spec_{{ $specialty->id }}"
                                                       {{ (is_array(old('specialties')) && in_array($specialty->id, old('specialties'))) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="spec_{{ $specialty->id }}">
                                                    {{ $specialty->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('specialties') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Footer de acciones --}}
                        <div class="col-12 mt-5 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.admin.doctors.index') }}" class="btn btn-link text-muted text-decoration-none small">
                                    Cancelar y volver
                                </a>
                                <button type="submit" class="btn btn-success px-5 shadow">
                                    <i class="bi bi-check-circle me-2"></i>Guardar Profesional
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
