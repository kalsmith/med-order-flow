@extends('layouts.admin')

@section('header', 'Configurar: ' . $examType->name)

@section('header-actions')
    <a href="{{ route('admin.exam-types.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Volver al listado
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.exam-types.update', $examType->id) }}" method="POST" id="examForm">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- COLUMNA IZQUIERDA: DATOS BÁSICOS --}}
            <div class="col-xl-4">
                <div class="card shadow-sm border-0 rounded-4 sticky-top" style="top: 20px;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                                <i class="bi bi-gear-fill text-primary"></i>
                            </div>
                            <h5 class="m-0 fw-bold">Ajustes Principales</h5>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small uppercase">Nombre Público</label>
                            <textarea name="name" class="form-control border-2 fw-bold" rows="2" required>{{ old('name', $examType->name) }}</textarea>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-muted small">Especialidad</label>
                                <select name="specialty_id" class="form-select border-2" required>
                                    @foreach($specialties as $specialty)
                                        <option value="{{ $specialty->id }}" {{ $examType->specialty_id == $specialty->id ? 'selected' : '' }}>
                                            {{ $specialty->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <label class="form-label fw-bold text-muted small">Precio Venta</label>
                                <div class="input-group">
                                    <span class="input-group-text border-2 bg-light fw-bold">$</span>
                                    <input type="number" name="base_price" class="form-control border-2 fw-bold text-primary" value="{{ old('base_price', $examType->base_price) }}" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-muted small">Cod. Fonasa</label>
                                <input type="text" name="code_fonasa" class="form-control border-2" value="{{ old('code_fonasa', $examType->code_fonasa) }}">
                            </div>
                        </div>

                        <div class="p-3 bg-light rounded-3 mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeSwitch" {{ $examType->is_active ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="activeSwitch">Visible para pacientes</label>
                            </div>
                        </div>

                        <div class="pt-3 border-top">
                            <label class="form-label fw-bold text-primary small uppercase mb-3 d-flex align-items-center">
                                <i class="bi bi-graph-up-arrow me-2"></i> SEO & Blog
                            </label>
                            <div class="mb-3">
                                <select name="post_id" class="form-select border-2">
                                    <option value="">-- Sin artículo --</option>
                                    @foreach($posts as $post)
                                        <option value="{{ $post->id }}" {{ (old('post_id', $examType->post_id) == $post->id) ? 'selected' : '' }}>
                                            {{ $post->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <textarea name="description" class="form-control border-2" rows="3" placeholder="Slogan SEO...">{{ old('description', $examType->description) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-4 shadow rounded-3 py-3 fw-bold">
                            <i class="bi bi-cloud-arrow-up-fill me-2"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: SELECCIÓN DE COMPOSICIÓN (LISTA LARGA) --}}
            <div class="col-xl-8">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-10 p-2 rounded-3 me-3">
                                    <i class="bi bi-stack text-info"></i>
                                </div>
                                <div>
                                    <h5 class="m-0 fw-bold">Composición de la Pila</h5>
                                    <p class="text-muted small mb-0">Selecciona los exámenes que integran este pack.</p>
                                </div>
                            </div>
                            <span id="counterBadge" class="badge rounded-pill bg-primary px-3 py-2">
                                0 seleccionados
                            </span>
                        </div>

                        {{-- Buscador Refinado --}}
                        <div class="input-group input-group-lg mb-3">
                            <span class="input-group-text bg-light border-2 border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" id="examSearch" class="form-control bg-light border-2 border-start-0"
                                   placeholder="Escribe para buscar exámenes por nombre o código...">
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="list-group list-group-flush overflow-auto" style="max-height: 700px;" id="examList">
                            @foreach($allExams as $item)
                                @if($item->id != $examType->id) {{-- Evitar que se agregue a sí mismo --}}
                                <label class="list-group-item list-group-item-action border-0 py-3 px-4 exam-item" style="cursor: pointer;">
                                    <div class="d-flex align-items-start">
                                        <div class="form-check me-3">
                                            <input class="form-check-input exam-checkbox" type="checkbox"
                                                   name="bundle_ids[]" value="{{ $item->id }}"
                                                   {{ $examType->children->contains($item->id) ? 'checked' : '' }}>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold text-dark item-name">{{ $item->name }}</div>
                                            <div class="d-flex gap-3 mt-1">
                                                <small class="text-muted"><i class="bi bi-hash me-1"></i>{{ $item->code_fonasa ?? 'Sin código' }}</small>
                                                <small class="text-primary fw-bold">${{ number_format($item->base_price, 0, ',', '.') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="card-footer bg-light border-0 p-3 text-center">
                        <small class="text-muted">Si el examen es único y no un pack, simplemente no selecciones ningún item de esta lista.</small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .uppercase { letter-spacing: 1px; font-size: 0.75rem; }
    .exam-item { transition: all 0.2s; border-bottom: 1px solid #f1f1f1 !important; }
    .exam-item:hover { background-color: #f8fbff !important; }
    .exam-item.selected { background-color: #eef6ff !important; }
    .item-name { line-height: 1.4; font-size: 0.95rem; }

    /* Scrollbar estético */
    #examList::-webkit-scrollbar { width: 8px; }
    #examList::-webkit-scrollbar-track { background: #f1f1f1; }
    #examList::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
    #examList::-webkit-scrollbar-thumb:hover { background: #bbb; }
</style>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('examSearch');
    const examItems = document.querySelectorAll('.exam-item');
    const counterBadge = document.getElementById('counterBadge');
    const checkboxes = document.querySelectorAll('.exam-checkbox');

    // Función para actualizar el contador y el estilo visual
    function updateUI() {
        const selectedCount = document.querySelectorAll('.exam-checkbox:checked').length;
        counterBadge.innerText = `${selectedCount} seleccionados`;

        checkboxes.forEach(cb => {
            const row = cb.closest('.exam-item');
            if (cb.checked) {
                row.classList.add('selected');
            } else {
                row.classList.remove('selected');
            }
        });
    }

    // Buscador en tiempo real
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        examItems.forEach(item => {
            const text = item.querySelector('.item-name').innerText.toLowerCase();
            const code = item.innerText.toLowerCase(); // Incluye el código
            item.style.display = (text.includes(query) || code.includes(query)) ? '' : 'none';
        });
    });

    // Evento al cambiar checkbox
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateUI);
    });

    // Ejecutar al cargar para marcar iniciales
    updateUI();
});
</script>
@endpush
@endsection
