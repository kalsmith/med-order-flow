@extends('layouts.admin')

@section('header', 'Gestión de Especialidades')

@section('header-actions')
<a href="{{ route('admin.specialties.create') }}" class="btn btn-primary shadow-sm">
    <i class="bi bi-plus-lg me-2"></i>Nueva Especialidad
</a>
@endsection

@section('content')

@if (session('status'))
    <div class="alert alert-success border-0 shadow-sm mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
    </div>
@endif

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Nombre de Especialidad</th>
                    <th>Slug Identificador</th>
                    <th>Descripción</th>
                    <th class="text-end pe-4">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($specialties as $specialty)
                <tr>
                    <td class="ps-4 text-muted small">#{{ $specialty->id }}</td>
                    <td>
                        <div class="fw-bold text-dark">{{ $specialty->name }}</div>
                    </td>
                    <td>
                        <span class="badge bg-light text-secondary border">
                            <i class="bi bi-link-45deg me-1"></i>{{ $specialty->slug }}
                        </span>
                    </td>
                    <td>
                        <span class="text-muted small">
                            {{ Str::limit($specialty->description, 60) ?: 'Sin descripción' }}
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group">
                            <a href="{{ route('admin.specialties.edit', $specialty) }}"
                               class="btn btn-sm btn-outline-primary"
                               title="Editar Especialidad">
                                <i class="bi bi-pencil"></i>
                            </a>
                            {{-- Podrías agregar un botón de eliminar aquí --}}
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-clipboard2-x display-4 d-block mb-3"></i>
                            No hay especialidades registradas.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
