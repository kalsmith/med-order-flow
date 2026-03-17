@extends('layouts.admin')

@section('header', 'Catálogo de Exámenes')

@section('header-actions')
    <a href="{{ route('admin.exam-types.create') }}" class="btn btn-primary btn-sm shadow-sm">
        <i class="bi bi-plus-circle me-2"></i> Nuevo Examen
    </a>
@endsection

@section('content')

{{-- Mensajes de Feedback --}}
@if (session('status'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px; background-color: #d1e7dd; color: #0f5132;">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
            <div>
                <strong>¡Actualizado!</strong> {{ session('status') }}
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <strong>Hubo un problema:</strong>
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form action="{{ route('admin.exam-types.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0"
                           placeholder="Nombre o Código Fonasa..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="specialty_id" class="form-select">
                    <option value="">Todas las Especialidades</option>
                    @foreach($specialties as $s)
                        <option value="{{ $s->id }}" {{ request('specialty_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select">
                    <option value="">Todos los Tipos</option>
                    <option value="individual" {{ request('type') == 'individual' ? 'selected' : '' }}>Exámenes Únicos</option>
                    <option value="pack" {{ request('type') == 'pack' ? 'selected' : '' }}>Packs / Pilas</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100">Filtrar</button>
                <a href="{{ route('admin.exam-types.index') }}" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 25%;">Examen / Batería</th>
                    <th>Especialidad</th>
                    <th>Código Fonasa</th>
                    <th>Composición / Inclusión</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($exams as $exam)
                <tr>
                    <td>
                        <div class="fw-bold text-dark">{{ $exam->name }}</div>
                        <small class="{{ $exam->children_count > 0 ? 'text-primary fw-semibold' : 'text-muted' }}">
                            <i class="bi {{ $exam->children_count > 0 ? 'bi-stack' : 'bi-circle' }} me-1"></i>
                            {{ $exam->children_count > 0 ? 'Pila de exámenes' : 'Examen único' }}
                        </small>
                    </td>
                    <td>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                            {{ $exam->specialty->name }}
                        </span>
                    </td>
                    <td><code>{{ $exam->code_fonasa ?? '—' }}</code></td>
                    <td>
                        @if($exam->children_count > 0)
                            <span class="badge rounded-pill bg-info-subtle text-info border border-info-subtle">
                                {{ $exam->children_count }} items vinculados
                            </span>
                        @elseif($exam->parents->count() > 0)
                            <div class="lh-sm">
                                @foreach($exam->parents as $parent)
                                    <span class="badge bg-light text-dark border fw-normal mb-1" style="font-size: 0.7rem;">
                                        {{ $parent->name }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td><strong>${{ number_format($exam->base_price, 0, ',', '.') }}</strong></td>
                    <td>
                        <span class="badge {{ $exam->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                            {{ $exam->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group">
                            <a href="{{ route('admin.exam-types.edit', ['exam_type' => $exam->id]) }}"
                               class="btn btn-sm btn-outline-primary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-search fs-1 d-block mb-3"></i>
                        No se encontraron exámenes con los criterios seleccionados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white border-0 py-3">
        {{ $exams->links() }}
    </div>
</div>

<style>
    /* Animación de entrada para la alerta */
    .alert {
        animation: slideDown 0.4s ease-out;
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-15px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
