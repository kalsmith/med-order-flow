@extends('layouts.admin')

@section('header', 'Gestión de Especialidades')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.specialties.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nueva Especialidad
    </a>
</div>

<div class="card shadow">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($specialties as $specialty)
                <tr>
                    <td>{{ $specialty->id }}</td>
                    <td><strong>{{ $specialty->name }}</strong></td>
                    <td><code>{{ $specialty->slug }}</code></td>
                    <td>{{ Str::limit($specialty->description, 50) }}</td>
                    <td>
                        <a href="{{ route('admin.specialties.edit', $specialty) }}" class="btn btn-sm btn-outline-info">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">No hay especialidades cargadas aún.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
