@extends('layouts.admin')

@section('header', 'Crear Nueva Especialidad')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">Información de la Especialidad</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('specialties.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre de la Especialidad</label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="Ej: Cardiología, Urología..."
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label">URL Amigable (Slug)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">/specialties/</span>
                            <input type="text"
                                   class="form-control @error('slug') is-invalid @enderror"
                                   id="slug"
                                   name="slug"
                                   value="{{ old('slug') }}"
                                   readonly>
                        </div>
                        <small class="form-text text-muted">Se genera automáticamente basado en el nombre.</small>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción (Opcional)</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="4"
                                  placeholder="Detalles sobre esta área médica...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('specialties.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar Especialidad
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    // Script simple para autogenerar el slug
    document.getElementById('name').addEventListener('input', function() {
        let name = this.value;
        let slug = name.toLowerCase()
                       .normalize("NFD")
                       .replace(/[\u0300-\u036f]/g, "") // Quitar tildes
                       .replace(/[^a-z0-0\s-]/g, "")    // Quitar caracteres especiales
                       .replace(/\s+/g, "-")            // Espacios por guiones
                       .replace(/-+/g, "-");            // Quitar guiones duplicados
        document.getElementById('slug').value = slug;
    });
</script>
@endpush
