@extends('layouts.admin')

@section('header', 'Editar Médico')

@section('header-actions')
    <a href="{{ route('admin.doctors.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route('admin.doctors.update', $doctor) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <h6 class="fw-bold border-bottom pb-2 mb-3">Información Personal</h6>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Nombre Completo</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $doctor->user->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $doctor->user->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">RUT</label>
                            <input type="text" name="rut" class="form-control @error('rut') is-invalid @enderror"
                                   value="{{ old('rut', $doctor->rut) }}" placeholder="12.345.678-9">
                            @error('rut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <h6 class="fw-bold border-bottom pb-2 mt-4 mb-3">Información Profesional</h6>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Registro RNPI (SuperSalud)</label>
                            <input type="text" name="rnpi_number" class="form-control @error('rnpi_number') is-invalid @enderror"
                                   value="{{ old('rnpi_number', $doctor->rnpi_number) }}">
                            @error('rnpi_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Estado</label>
                            <select name="is_active" class="form-select">
                                <option value="1" {{ $doctor->is_active ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ !$doctor->is_active ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Especialidades</label>
                            <div class="d-flex flex-wrap gap-3 p-3 border rounded bg-light">
                                @foreach($specialties as $specialty)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="specialties[]"
                                               value="{{ $specialty->id }}" id="spec_{{ $specialty->id }}"
                                               {{ $doctor->specialties->contains($specialty->id) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="spec_{{ $specialty->id }}">
                                            {{ $specialty->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Firma Digital (Imagen PNG)</label>
                            <input type="file" name="signature" class="form-control">
                            @if($doctor->signature_path)
                                <div class="mt-2">
                                    <small class="text-muted d-block mb-1">Firma actual:</small>
                                    <img src="{{ Storage::url($doctor->signature_path) }}" alt="Firma" class="img-thumbnail" style="height: 50px;">
                                </div>
                            @endif
                        </div>

                        <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-2"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
