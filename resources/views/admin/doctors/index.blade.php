@extends('layouts.admin')

@section('header', 'Cuerpo Médico')

@section('header-actions')
    <a href="{{ route('admin.doctors.create') }}" class="btn btn-primary shadow-sm">
        <i class="bi bi-person-plus-fill me-2"></i>Nuevo Doctor
    </a>
@endsection

@section('content')

{{-- Alertas de estado --}}
@if (session('status'))
    <div class="alert alert-success border-0 shadow-sm mb-4 alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Doctor</th>
                    <th>RUT / SIS</th>
                    <th>Especialidades</th>
                    <th class="text-center">Firma</th>
                    <th>Estado</th>
                    <th class="text-end pe-4">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($doctors as $doctor)
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold; border: 1px solid rgba(var(--bs-primary-rgb), 0.2);">
                                {{ strtoupper(substr($doctor->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-bold text-dark">{{ $doctor->user->name }}</div>
                                <div class="text-muted small">{{ $doctor->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-medium text-secondary small">{{ $doctor->rut }}</div>
                        <div class="badge bg-light text-muted border font-monospace" style="font-size: 0.7rem;">
                            {{ $doctor->rnpi_number ?? 'SIN RNPI' }}
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @forelse($doctor->specialties as $esp)
                                <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size: 0.75rem;">
                                    {{ $esp->name }}
                                </span>
                            @empty
                                <span class="text-muted small italic">Sin especialidad</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="text-center">
                        @if($doctor->signature_path)
                            <span class="text-success" title="Firma cargada" data-bs-toggle="tooltip">
                                <i class="bi bi-check-circle-fill fs-5"></i>
                            </span>
                        @else
                            <span class="text-warning" title="Firma pendiente" data-bs-toggle="tooltip">
                                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($doctor->is_active)
                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">
                                <i class="bi bi-dot"></i> Activo
                            </span>
                        @else
                            <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle">
                                <i class="bi bi-dot"></i> Inactivo
                            </span>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group shadow-sm">
                            {{-- CORRECCIÓN: Se pasa el objeto $doctor completo para que el Resource resuelva el parámetro {medico} --}}
                            <a href="{{ route('admin.doctors.edit', $doctor) }}" class="btn btn-sm btn-white border" title="Editar perfil" data-bs-toggle="tooltip">
                                <i class="bi bi-pencil-square text-primary"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Paginación (Si la usas en el controlador) --}}
    @if($doctors->hasPages())
    <div class="card-footer bg-white border-top-0 py-3">
        {{ $doctors->links() }}
    </div>
    @endif
</div>

@endsection
