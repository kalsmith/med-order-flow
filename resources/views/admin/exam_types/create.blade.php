@extends('layouts.admin')

@section('header', 'Nuevo Examen o Pila')

@section('header-actions')
    <a href="{{ route('admin.exam-types.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <form action="{{ route('admin.exam-types.store') }}" method="POST" id="examForm">
            @csrf

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4 text-dark fw-bold">Información General</h5>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Nombre del Examen o Pack</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Ej: Checkup Vida Sana o Hemograma" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- NUEVO: Campo de Slogan / Bajada SEO --}}
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Slogan / Bajada de Beneficio (SEO)</label>
                            <input type="text" id="descriptionInput" name="description" class="form-control @error('description') is-invalid @enderror"
                                   value="{{ old('description') }}"
                                   placeholder="Ej: Detecta a tiempo riesgos metabólicos y mejora tu energía diaria.">
                            <small class="text-muted">Esta frase es clave para convertir lectores del blog en pacientes.</small>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Especialidad</label>
                            <select name="specialty_id" class="form-select @error('specialty_id') is-invalid @enderror" required>
                                <option value="">Seleccione...</option>
                                @foreach($specialties as $specialty)
                                    <option value="{{ $specialty->id }}" {{ old('specialty_id') == $specialty->id ? 'selected' : '' }}>
                                        {{ $specialty->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('specialty_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Código Fonasa (Padre)</label>
                            <input type="text" name="code_fonasa" class="form-control" value="{{ old('code_fonasa') }}" placeholder="0404001">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Precio de Venta ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="base_price" class="form-control @error('base_price') is-invalid @enderror"
                                       value="{{ old('base_price', 0) }}" required>
                            </div>
                            <small class="text-muted">Si es una pila, este es el cobro total del pack.</small>
                            @error('base_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sección de Composición (Pila con Dual Listbox) --}}
            <div class="card shadow-sm border-0 border-start border-primary border-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-stack fs-4 text-primary me-2"></i>
                        <h5 class="card-title m-0 text-dark fw-bold">Configurar Pila de Exámenes</h5>
                    </div>

                    <div class="row">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-muted text-uppercase">Exámenes Disponibles</label>
                            <input type="text" id="searchAvailable" class="form-control form-control-sm mb-2" placeholder="Buscar examen...">
                            <select id="availableExams" class="form-select" multiple style="height: 250px;">
                                @foreach($allExams as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->name }} {{ $item->code_fonasa ? "($item->code_fonasa)" : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                            <button type="button" id="btnAdd" class="btn btn-outline-primary mb-2 w-100 shadow-sm">
                                <i class="bi bi-chevron-right d-none d-md-inline"></i>
                                <i class="bi bi-chevron-down d-md-none"></i>
                            </button>
                            <button type="button" id="btnRemove" class="btn btn-outline-danger w-100 shadow-sm">
                                <i class="bi bi-chevron-left d-none d-md-inline"></i>
                                <i class="bi bi-chevron-up d-md-none"></i>
                            </button>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-primary text-uppercase">Incluidos en el Pack</label>
                            <div class="mb-2" style="height: 31px;"></div>
                            <select id="selectedExams" name="bundle_ids[]" class="form-select border-primary" multiple style="height: 250px;">
                                {{-- Aquí se moverán los exámenes seleccionados --}}
                            </select>
                            <div class="form-text mt-2 d-flex justify-content-between">
                                <span><span id="countSelected" class="badge bg-primary">0</span> exámenes seleccionados</span>
                                <span id="packBadge" class="badge bg-info text-dark d-none">MODO PACK ACTIVO</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-5 shadow-sm btn-lg">
                    <i class="bi bi-check-lg me-2"></i> Guardar Examen / Pack
                </button>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const availableSelect = document.getElementById('availableExams');
    const selectedSelect = document.getElementById('selectedExams');
    const btnAdd = document.getElementById('btnAdd');
    const btnRemove = document.getElementById('btnRemove');
    const searchInput = document.getElementById('searchAvailable');
    const form = document.getElementById('examForm');
    const descriptionInput = document.getElementById('descriptionInput');
    const packBadge = document.getElementById('packBadge');

    function moveOptions(from, to) {
        const selectedOptions = Array.from(from.selectedOptions);
        selectedOptions.forEach(option => {
            to.appendChild(option);
            option.selected = false;
        });
        updateCounter();
        sortSelect(to);
    }

    function updateCounter() {
        const count = selectedSelect.options.length;
        document.getElementById('countSelected').innerText = count;

        if (count > 0) {
            // Estética "agresiva": resaltamos que es un pack
            descriptionInput.classList.add('border-info', 'bg-light');
            packBadge.classList.remove('d-none');
        } else {
            descriptionInput.classList.remove('border-info', 'bg-light');
            packBadge.classList.add('d-none');
        }
    }

    function sortSelect(select) {
        const tmp = Array.from(select.options);
        tmp.sort((a, b) => a.text.localeCompare(b.text));
        select.innerHTML = '';
        tmp.forEach(opt => select.add(opt));
    }

    btnAdd.addEventListener('click', () => moveOptions(availableSelect, selectedSelect));
    btnRemove.addEventListener('click', () => moveOptions(selectedSelect, availableSelect));

    availableSelect.addEventListener('dblclick', () => moveOptions(availableSelect, selectedSelect));
    selectedSelect.addEventListener('dblclick', () => moveOptions(selectedSelect, availableSelect));

    searchInput.addEventListener('input', function() {
        const term = this.value.toLowerCase();
        Array.from(availableSelect.options).forEach(option => {
            const text = option.text.toLowerCase();
            option.style.display = text.includes(term) ? '' : 'none';
        });
    });

    form.addEventListener('submit', function() {
        Array.from(selectedSelect.options).forEach(option => option.selected = true);
    });
});
</script>
@endpush
@endsection
