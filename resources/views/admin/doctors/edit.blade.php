@extends('layouts.admin')

@section('header', 'Editar Perfil del Médico')

@section('header-actions')
    <a href="{{ route('admin.doctors.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver al listado
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9 col-lg-8">

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
            {{-- Indicador visual de estado --}}
            <div class="bg-primary py-1"></div>

            <div class="card-body p-4 p-md-5">
                {{-- CORRECCIÓN: Se pasa el objeto $doctor directamente para que Laravel use el parámetro 'medico' --}}
                <form action="{{ route('admin.doctors.update', ['medico' => $doctor->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        {{-- Sección: Información Personal --}}
                        <div class="col-12">
                            <h6 class="text-primary text-uppercase fw-bold small mb-3">
                                <i class="bi bi-person-badge me-2"></i>Información Personal
                            </h6>
                            <hr class="mt-0 opacity-10">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">Nombre Completo</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="name" class="form-control border-start-0 ps-0 @error('name') is-invalid @enderror"
                                       value="{{ old('name', $doctor->user->name) }}" required placeholder="Ej: Dr. Juan Pérez">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror"
                                       value="{{ old('email', $doctor->user->email) }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">RUT</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-card-text text-muted"></i></span>
                                <input type="text" name="rut" class="form-control border-start-0 ps-0 @error('rut') is-invalid @enderror"
                                       value="{{ old('rut', $doctor->rut) }}" placeholder="12.345.678-k">
                            </div>
                        </div>

                        {{-- Sección: Información Profesional --}}
                        <div class="col-12 mt-5">
                            <h6 class="text-primary text-uppercase fw-bold small mb-3">
                                <i class="bi bi-briefcase me-2"></i>Información Profesional
                            </h6>
                            <hr class="mt-0 opacity-10">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">N° Registro RNPI</label>
                            <input type="text" name="rnpi_number" class="form-control @error('rnpi_number') is-invalid @enderror"
                                   value="{{ old('rnpi_number', $doctor->rnpi_number) }}" placeholder="Opcional">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Estado del Médico</label>
                            <select name="is_active" class="form-select">
                                <option value="1" {{ $doctor->is_active ? 'selected' : '' }}>🟢 Activo / Vigente</option>
                                <option value="0" {{ !$doctor->is_active ? 'selected' : '' }}>🔴 Inactivo</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">Especialidades Asignadas</label>
                            <div class="row p-3 border rounded bg-light mx-0">
                                @foreach($specialties as $specialty)
                                    <div class="col-md-4 col-6 mb-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="specialties[]"
                                                   value="{{ $specialty->id }}" id="spec_{{ $specialty->id }}"
                                                   {{ $doctor->specialties->contains($specialty->id) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="spec_{{ $specialty->id }}">
                                                {{ $specialty->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Sección: Firma Digital --}}
                        <div class="col-md-12 mt-4">
                            <label class="form-label fw-bold small text-muted">Firma Digital (Sello)</label>
                            <div class="d-flex align-items-center gap-4 p-3 border rounded">
                                <div class="text-center bg-white p-2 border rounded" style="min-width: 150px;">
                                    <small class="text-muted d-block mb-2">Vista Previa</small>
                                    <img id="signature-preview"
                                         src="{{ $doctor->signature_path ? Storage::url($doctor->signature_path) : 'https://via.placeholder.com/150x80?text=Sin+Firma' }}"
                                         alt="Firma"
                                         class="img-fluid"
                                         style="max-height: 80px; object-fit: contain;">
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="signature" id="signature-input"
                                           class="form-control form-control-sm @error('signature') is-invalid @enderror"
                                           accept="image/png, image/jpeg">
                                    <p class="text-muted mb-0 mt-2" style="font-size: 0.75rem;">
                                        <i class="bi bi-info-circle me-1"></i> Se recomienda formato PNG transparente de 300x150px. Máximo 1MB.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Botones de Acción --}}
                        <div class="col-12 mt-5 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-link text-danger text-decoration-none small" onclick="confirmDelete()">
                                    <i class="bi bi-trash me-1"></i> Desactivar Médico
                                </button>
                                <button type="submit" class="btn btn-primary px-5 shadow">
                                    <i class="bi bi-save me-2"></i> Actualizar Perfil
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Script para previsualizar la firma antes de subirla --}}
<script>
    document.getElementById('signature-input').onchange = function (evt) {
        const [file] = this.files;
        if (file) {
            document.getElementById('signature-preview').src = URL.createObjectURL(file);
        }
    }
</script>
@endsection
