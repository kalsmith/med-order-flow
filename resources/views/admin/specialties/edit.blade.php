@extends('layouts.admin')

@section('header', 'Editar Especialidad: ' . $specialty->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-body">
                <form action="{{ route('admin.specialties.update', $specialty) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre de la Especialidad</label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name"
                               value="{{ old('name', $specialty->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug (URL)</label>
                        <input type="text" class="form-control bg-light" id="slug" name="slug"
                               value="{{ $specialty->slug }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $specialty->description) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.specialties.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Guardar Cambios
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
    // Mantenemos el generador de slug dinámico por si cambias el nombre
    document.getElementById('name').addEventListener('input', function() {
        let name = this.value;
        let slug = name.toLowerCase()
                       .normalize("NFD")
                       .replace(/[\u0300-\u036f]/g, "")
                       .replace(/[^a-z0-0\s-]/g, "")
                       .replace(/\s+/g, "-")
                       .replace(/-+/g, "-");
        document.getElementById('slug').value = slug;
    });
</script>
@endpush
