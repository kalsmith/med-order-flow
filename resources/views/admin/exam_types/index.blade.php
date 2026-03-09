@extends('layouts.admin')

@section('header', 'Catálogo de Exámenes')

@section('header-actions')
    <a href="{{ route('admin.exam-types.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-2"></i>Nuevo Examen
    </a>
@endsection

@section('content')
<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Examen / Batería</th>
                    <th>Especialidad</th>
                    <th>Código Fonasa</th>
                    <th>Composición</th>
                    <th>Precio Base</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($exams as $exam)
                <tr>
                    <td>
                        <div class="fw-bold text-dark">{{ $exam->name }}</div>
                        @if($exam->children_count > 0)
                            <small class="text-primary"><i class="bi bi-stack me-1"></i>Pila de exámenes</small>
                        @else
                            <small class="text-muted"><i class="bi bi-circle me-1"></i>Examen único</small>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                            {{ $exam->specialty->name }}
                        </span>
                    </td>
                    <td><code>{{ $exam->code_fonasa ?? 'N/A' }}</code></td>
                    <td>
                        @if($exam->children_count > 0)
                            <span class="badge rounded-pill bg-info-subtle text-info border border-info-subtle">
                                {{ $exam->children_count }} items
                            </span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td><strong>${{ number_format($exam->base_price, 0, ',', '.') }}</strong></td>
                    <td>
                        @if($exam->is_active)
                            <span class="badge bg-success-subtle text-success">Activo</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group">
                            <a href="{{ route('admin.exam-types.edit', $exam) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            {{-- Podrías añadir un botón de borrado aquí si lo deseas --}}
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white border-0 py-3">
        {{ $exams->links() }}
    </div>
</div>
@endsection
