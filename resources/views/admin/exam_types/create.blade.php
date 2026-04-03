@extends('layouts.admin')

@section('header', 'Nuevo Examen o Pila')

@section('header-actions')
    <a href="{{ route('admin.exam-types.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Volver al listado
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.exam-types.store') }}" method="POST" id="examForm">
        @csrf

        <div class="row g-4">
            {{-- COLUMNA IZQUIERDA: DATOS BÁSICOS --}}
            <div class="col-xl-4">
                <div class="card shadow-sm border-0 rounded-4 sticky-top" style="top: 20px; z-index: 10;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                                <i class="bi bi-plus-circle-fill text-primary"></i>
                            </div>
                            <h5 class="m-0 fw-bold">Configuración Inicial</h5>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small uppercase">Nombre del Examen o Pack</label>
                            <textarea name="name" class="form-control border-2 fw-bold @error('name') is-invalid @enderror"
                                      rows="2" placeholder="Ej: Perfil Bioquímico o Pack Mujer" required>{{ old('name') }}</textarea>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small">Especialidad</label>
                            <select name="specialty_id" class="form-select border-2 @error('specialty_id') is-invalid @enderror" required>
                                <option value="">Seleccione especialidad...</option>
                                @foreach($specialties as $specialty)
                                    <option value="{{ $specialty->id }}" {{ old('specialty_id') == $specialty->id ? 'selected' : '' }}>
                                        {{ $specialty->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <label class="form-label fw-bold text-muted small">Precio Venta ($)</label>
                                <div class="input-group">
                                    <span class="input-group-text border-2 bg-light fw-bold">$</span>
                                    <input type="number" name="base_price" class="form-control border-2 fw-bold text-primary"
                                           value="{{ old('base_price', 0) }}" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-muted small">Cod. Fonasa</label>
                                <input type="text" name="code_fonasa" class="form-control border-2"
                                       value="{{ old('code_fonasa') }}" placeholder="Opcional">
                            </div>
                        </div>

                        <div class="pt-3 border-top">
                            <label class="form-label fw-bold text-primary small uppercase mb-3 d-flex align-items-center">
                                <i class="bi bi-journal-text me-2"></i> SEO & Marketing
                            </label>
                            <div class="mb-3">
                                <select name="post_id" class="form-select border-2">
                                    <option value="">-- Sin artículo vinculado --</option>
                                    @foreach($posts as $post)
                                        <option value="{{ $post->id }}" {{ old('post_id') == $post->id ? 'selected' : '' }}>
                                            {{ $post->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <textarea name="description" class="form-control border-2" rows="3"
                                      placeholder="Slogan o bajada comercial para la web...">{{ old('description') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 mt-4 shadow rounded-3 fw-bold py-3">
                            <i class="bi bi-check-lg me-2"></i> Crear Registro
                        </button>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: SELECCIÓN DE COMPOSICIÓN --}}
            <div class="col-xl-8">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-10 p-2 rounded-3 me-3">
                                    <i class="bi bi-stack text-info"></i>
                                </div>
                                <div>
                                    <h5 class="m-0 fw-bold">Composición del Pack</h5>
                                    <p class="text-muted small mb-0">Selecciona los exámenes que componen esta pila (deja vacío si es examen único).</p>
                                </div>
                            </div>
                            <span id="counterBadge" class="badge rounded-pill bg-primary px-3 py-2 fs-6">
                                0 seleccionados
                            </span>
                        </div>

                        <div class="input-group input-group-lg mb-3">
                            <span class="input-group-text bg-light border-2 border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" id="examSearch" class="form-control bg-light border-2 border-start-0"
                                   placeholder="Buscar por nombre o código Fonasa...">
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="list-group list-group-flush overflow-auto" style="max-height: 700px;" id="examList">
                            @foreach($allExams as $item)
                                <label class="list-group-item list-group-item-action border-0 py-3 px-4 exam-item" style="cursor: pointer;">
                                    <div class="d-flex align-items-start">
                                        <div class="form-check me-3">
                                            <input class="form-check-input exam-checkbox" type="checkbox"
                                                   name="bundle_ids[]" value="{{ $item->id }}">
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold text-dark item-name">{{ $item->name }}</div>
                                            <div class="d-flex gap-3 mt-1">
                                                <small class="text-muted"><i class="bi bi-hash me-1"></i>{{ $item->code_fonasa ?? 'S/C' }}</small>
                                                <small class="text-primary fw-bold">${{ number_format($item->base_price, 0, ',', '.') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
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
    .exam-item.selected { background-color: #f0f7ff !important; border-left: 4px solid #0d6efd !important; }
    #examList::-webkit-scrollbar { width: 8px; }
    #examList::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
</style>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('examSearch');
    const examItems = document.querySelectorAll('.exam-item');
    const counterBadge = document.getElementById('counterBadge');
    const checkboxes = document.querySelectorAll('.exam-checkbox');

    function updateUI() {
        const selectedCount = document.querySelectorAll('.exam-checkbox:checked').length;
        counterBadge.innerText = `${selectedCount} seleccionados`;
        checkboxes.forEach(cb => {
            const row = cb.closest('.exam-item');
            if (cb.checked) row.classList.add('selected');
            else row.classList.remove('selected');
        });
    }

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        examItems.forEach(item => {
            item.style.display = item.innerText.toLowerCase().includes(query) ? '' : 'none';
        });
    });

    checkboxes.forEach(cb => cb.addEventListener('change', updateUI));
});
</script>
@endpush
@endsection
