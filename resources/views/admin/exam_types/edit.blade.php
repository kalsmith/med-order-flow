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

        {{-- Bloque de Alertas para Feedback Visual --}}
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-4 me-2"></i>
                    <div>{{ session('status') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                    <div>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('admin.exam-types.update', $examType->id) }}" method="POST" id="examForm">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4 text-dark fw-bold">Información General</h5>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nombre del Pack / Examen</label>
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
                                <label class="form-label fw-semibold">Código Fonasa</label>
                                <input type="text" name="code_fonasa" class="form-control" value="{{ old('code_fonasa', $examType->code_fonasa) }}">
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
                                <h5 class="card-title m-0 text-dark fw-bold">Composición de la Pila (Pack)</h5>
                            </div>

                            <div class="row align-items-center mt-4">
                                <div class="col-5">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Disponibles</label>
                                    <input type="text" id="search_available" class="form-control form-control-sm mb-2" placeholder="Buscar examen...">

                                    <select id="available_exams" class="form-select" size="12" multiple>
                                        @foreach($allExams as $item)
                                            @if(!$examType->children->contains($item->id))
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-2 text-center mt-5">
                                    <div class="d-grid gap-2">
                                        <button type="button" id="btn_add" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-chevron-right"></i>
                                        </button>
                                        <button type="button" id="btn_remove" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-chevron-left"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-5">
                                    <label class="form-label fw-bold small text-uppercase text-primary">En esta Pila</label>
                                    <div style="height: 31px;" class="mb-2"></div>
                                    <select name="bundle_ids[]" id="selected_exams" class="form-select border-primary" size="12" multiple>
                                        @foreach($examType->children as $child)
                                            <option value="{{ $child->id }}">{{ $child->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <p class="text-muted small mt-3">
                                <i class="bi bi-info-circle me-1"></i> Selecciona y usa las flechas o haz doble clic para mover.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnAdd = document.getElementById('btn_add');
    const btnRemove = document.getElementById('btn_remove');
    const available = document.getElementById('available_exams');
    const selected = document.getElementById('selected_exams');
    const search = document.getElementById('search_available');
    const form = document.getElementById('examForm');

    function move(from, to) {
        Array.from(from.selectedOptions).forEach(opt => to.appendChild(opt));
        sortSelect(to);
    }

    function sortSelect(sel) {
        const tmp = Array.from(sel.options);
        tmp.sort((a, b) => a.text.localeCompare(b.text));
        sel.innerHTML = '';
        tmp.forEach(opt => sel.add(opt));
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

    // IMPORTANTE: Antes de enviar, seleccionamos todos para que viajen en el Request
    form.addEventListener('submit', () => {
        Array.from(selected.options).forEach(opt => opt.selected = true);
    });
});
</script>
@endpush

<style>
    .form-select[size] { height: 350px !important; }
    .alert { animation: fadeInDown 0.5s ease-out; }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
