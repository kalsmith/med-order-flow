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
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                                <i class="bi bi-gear-fill text-primary"></i>
                            </div>
                            <h5 class="m-0 fw-bold">Ajustes Principales</h5>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small uppercase">Nombre Público</label>
                            <input type="text" name="name" class="form-control form-control-lg border-2" value="{{ old('name', $examType->name) }}" required>
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
                            <label class="form-label fw-bold text-muted small d-block mb-2">Visibilidad en Web</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeSwitch" {{ $examType->is_active ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="activeSwitch">Examen visible para pacientes</label>
                            </div>
                        </div>

                        {{-- SECCIÓN SEO & BLOG INTEGRADA --}}
                        <div class="pt-3 border-top">
                            <label class="form-label fw-bold text-primary small uppercase mb-3 d-flex align-items-center">
                                <i class="bi bi-graph-up-arrow me-2"></i> Estrategia SEO & Blog
                            </label>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Vincular Artículo Educativo</label>
                                <select name="post_id" class="form-select border-2 bg-white">
                                    <option value="">-- Sin artículo --</option>
                                    @foreach($posts as $post)
                                        <option value="{{ $post->id }}" {{ (old('post_id', $examType->post_id) == $post->id) ? 'selected' : '' }}>
                                            {{ $post->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Si seleccionas uno, la Card en la Home enlazará a este post.</div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-semibold small">Slogan de Venta (SEO)</label>
                                <textarea name="description" id="descriptionInput" class="form-control border-2" rows="3"
                                    placeholder="Frase corta para convencer al paciente...">{{ old('description', $examType->description) }}</textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-4 shadow rounded-3 py-3 fw-bold">
                            <i class="bi bi-cloud-arrow-up-fill me-2"></i> Actualizar Configuración
                        </button>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: COMPOSICIÓN DEL PACK --}}
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden h-100">
                    <div class="card-header bg-white border-0 p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-10 p-2 rounded-3 me-3">
                                    <i class="bi bi-stack text-info"></i>
                                </div>
                                <div>
                                    <h5 class="m-0 fw-bold">Composición del Pack</h5>
                                    <p class="text-muted small mb-0">Gestiona qué exámenes individuales conforman esta batería.</p>
                                </div>
                            </div>
                            <div id="badgeStatus" class="badge rounded-pill bg-light text-dark border px-3 py-2 d-none">
                                <i class="bi bi-box-fill me-1"></i> MODO PACK
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4 bg-light bg-opacity-50">
                        <div class="row g-4 align-items-stretch">
                            {{-- Disponibles --}}
                            <div class="col-md-5">
                                <div class="card border-0 shadow-none bg-white h-100 rounded-3">
                                    <div class="card-body">
                                        <label class="form-label fw-bold small text-muted text-uppercase mb-2">Exámenes Disponibles</label>
                                        <div class="input-group input-group-sm mb-3">
                                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                                            <input type="text" id="search_available" class="form-control border-start-0 ps-0" placeholder="Filtrar por nombre...">
                                        </div>
                                        <select id="available_exams" class="form-select border-0 bg-transparent custom-listbox" size="15" multiple>
                                            @foreach($allExams as $item)
                                                @if(!$examType->children->contains($item->id) && $item->id != $examType->id)
                                                    <option value="{{ $item->id }}" class="py-2 px-3 border-bottom">{{ $item->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Botones de acción --}}
                            <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                <div class="d-grid gap-3 w-100 px-2">
                                    <button type="button" id="btn_add" class="btn btn-white shadow-sm border py-3 rounded-3 hover-primary">
                                        <i class="bi bi-chevron-right fs-4"></i>
                                    </button>
                                    <button type="button" id="btn_remove" class="btn btn-white shadow-sm border py-3 rounded-3 hover-danger">
                                        <i class="bi bi-chevron-left fs-4"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Seleccionados --}}
                            <div class="col-md-5">
                                <div class="card border-primary border-opacity-25 shadow-none bg-white h-100 rounded-3">
                                    <div class="card-body">
                                        <label class="form-label fw-bold small text-primary text-uppercase mb-2">Incluidos en esta Pila</label>
                                        <div class="mb-3" style="height: 31px;"></div> {{-- Spacer --}}
                                        <select name="bundle_ids[]" id="selected_exams" class="form-select border-0 bg-transparent custom-listbox" size="15" multiple>
                                            @foreach($examType->children as $child)
                                                <option value="{{ $child->id }}" class="py-2 px-3 border-bottom fw-semibold text-primary">
                                                    {{ $child->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="card-footer bg-primary bg-opacity-10 border-0">
                                        <small class="fw-bold text-primary"><span id="countSelected">0</span> exámenes integrados</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .custom-listbox { min-height: 450px !important; outline: none; }
    .custom-listbox option { transition: all 0.2s; cursor: pointer; }
    .custom-listbox option:hover { background: #f8f9fa; }
    .hover-primary:hover { border-color: #0d6efd !important; color: #0d6efd; background: #f0f7ff; }
    .hover-danger:hover { border-color: #dc3545 !important; color: #dc3545; background: #fff5f5; }
    #descriptionInput { transition: border-color 0.3s ease; }
    .uppercase { letter-spacing: 1px; font-size: 0.75rem; }
</style>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnAdd = document.getElementById('btn_add');
    const btnRemove = document.getElementById('btn_remove');
    const available = document.getElementById('available_exams');
    const selected = document.getElementById('selected_exams');
    const search = document.getElementById('search_available');
    const form = document.getElementById('examForm');
    const countSpan = document.getElementById('countSelected');
    const badgeStatus = document.getElementById('badgeStatus');

    function move(from, to) {
        Array.from(from.selectedOptions).forEach(opt => {
            opt.classList.toggle('text-primary');
            opt.classList.toggle('fw-semibold');
            to.appendChild(opt);
        });
        sortSelect(to);
        updateUI();
    }

    function sortSelect(sel) {
        const tmp = Array.from(sel.options);
        tmp.sort((a, b) => a.text.localeCompare(b.text));
        sel.innerHTML = '';
        tmp.forEach(opt => sel.add(opt));
    }

    function updateUI() {
        const count = selected.options.length;
        countSpan.innerText = count;
        if (count > 0) {
            badgeStatus.classList.remove('d-none');
            document.getElementById('descriptionInput').classList.add('border-primary-subtle');
        } else {
            badgeStatus.classList.add('d-none');
            document.getElementById('descriptionInput').classList.remove('border-primary-subtle');
        }
    }

    btnAdd.addEventListener('click', () => move(available, selected));
    btnRemove.addEventListener('click', () => move(selected, available));
    available.addEventListener('dblclick', () => move(available, selected));
    selected.addEventListener('dblclick', () => move(selected, available));

    search.addEventListener('input', function() {
        const f = this.value.toLowerCase();
        Array.from(available.options).forEach(opt => {
            opt.style.display = opt.text.toLowerCase().includes(f) ? '' : 'none';
        });
    });

    form.addEventListener('submit', () => {
        Array.from(selected.options).forEach(opt => opt.selected = true);
    });

    updateUI();
});
</script>
@endpush
@endsection
