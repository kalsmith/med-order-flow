@extends('layouts.admin')

@section('header', 'Configurar Batería de Exámenes')

@section('header-actions')
    <a href="{{ route('admin.exam-types.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-11">
        <form action="{{ route('admin.exam-types.update', $examType) }}" method="POST" id="examForm">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4 text-dark fw-bold">Información General</h5>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nombre del Pack</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $examType->name) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Especialidad</label>
                                <select name="specialty_id" class="form-select" required>
                                    @foreach($specialties as $specialty)
                                        <option value="{{ $specialty->id }}" {{ $examType->specialty_id == $specialty->id ? 'selected' : '' }}>
                                            {{ $specialty->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Precio de Venta ($)</label>
                                <input type="number" name="base_price" class="form-control" value="{{ old('base_price', $examType->base_price) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Estado</label>
                                <select name="is_active" class="form-select">
                                    <option value="1" {{ $examType->is_active ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ !$examType->is_active ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mt-3 shadow-sm">
                                <i class="bi bi-save me-2"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-stack fs-4 text-primary me-2"></i>
                                <h5 class="card-title m-0 text-dark fw-bold">Composición de la Pila</h5>
                            </div>

                            <div class="row align-items-center mt-4">
                                <div class="col-5">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Disponibles</label>
                                    <select id="available_exams" class="form-select" size="12" multiple>
                                        @foreach($allExams as $item)
                                            @if(!$examType->children->contains($item->id))
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-2 text-center mt-4">
                                    <div class="d-grid gap-2">
                                        <button type="button" id="btn_add" class="btn btn-outline-primary btn-sm px-1">
                                            Agregar <i class="bi bi-chevron-right"></i>
                                        </button>
                                        <button type="button" id="btn_remove" class="btn btn-outline-danger btn-sm px-1">
                                            <i class="bi bi-chevron-left"></i> Quitar
                                        </button>
                                    </div>
                                </div>

                                <div class="col-5">
                                    <label class="form-label fw-bold small text-uppercase text-primary">En esta Pila</label>
                                    <select name="bundle_ids[]" id="selected_exams" class="form-select border-primary" size="12" multiple>
                                        @foreach($examType->children as $child)
                                            <option value="{{ $child->id }}">{{ $child->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <p class="text-muted small mt-3">
                                <i class="bi bi-info-circle me-1"></i> Mueve los exámenes de izquierda a derecha para incluirlos en el pack.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnAdd = document.getElementById('btn_add');
    const btnRemove = document.getElementById('btn_remove');
    const available = document.getElementById('available_exams');
    const selected = document.getElementById('selected_exams');
    const form = document.getElementById('examForm');

    // Mover de disponibles a seleccionados
    btnAdd.addEventListener('click', () => {
        let options = Array.from(available.selectedOptions);
        options.forEach(opt => selected.appendChild(opt));
    });

    // Mover de seleccionados a disponibles
    btnRemove.addEventListener('click', () => {
        let options = Array.from(selected.selectedOptions);
        options.forEach(opt => available.appendChild(opt));
    });

    // Truco: Seleccionar todos antes de enviar para que Laravel reciba el array completo
    form.addEventListener('submit', () => {
        Array.from(selected.options).forEach(opt => opt.selected = true);
    });
});
</script>

<style>
    .form-select[size] { height: 350px; overflow-y: auto; }
    .btn-outline-primary:hover, .btn-outline-danger:hover { color: white !important; }
</style>
@endsection
