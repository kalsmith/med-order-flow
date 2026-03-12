@extends('layouts.admin')

@section('header', 'Cuerpo Médico')

@section('header-actions')
    <a href="{{ route('admin.doctors.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus-fill me-2"></i>Nuevo Doctor
    </a>
@endsection

@section('content')

{{-- Bloque de alertas para confirmar acciones exitosas --}}
@if (session('status'))
    <div class="alert alert-success border-0 shadow-sm mb-4">
        {{ session('status') }}
    </div>
@endif

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Doctor</th>
                    <th>RUT / SIS</th>
                    <th>Especialidades</th>
                    <th>Firma</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($doctors as $doctor)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                {{ substr($doctor->user->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="fw-bold">{{ $doctor->user->name }}</div>
                                <div class="text-muted small">{{ $doctor->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-medium">{{ $doctor->rut }}</div>
                        <div class="badge bg-light text-dark border">{{ $doctor->rnpi_number ?? 'Sin RNPI' }}</div>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @forelse($doctor->specialties as $esp)
                                <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $esp->name }}</span>
                            @empty
                                <span class="text-muted small">Sin especialidad</span>
                            @endforelse
                        </div>
                    </td>
                    <td>
                        @if($doctor->signature_path)
                            <span class="text-success" title="Firma cargada">
                                <i class="bi bi-patch-check-fill"></i>
                            </span>
                        @else
                            <span class="text-warning" title="Firma pendiente">
                                <i class="bi bi-exclamation-triangle"></i>
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($doctor->is_active)
                            <span class="badge rounded-pill bg-success-subtle text-success">Activo</span>
                        @else
                            <span class="badge rounded-pill bg-danger-subtle text-danger">Inactivo</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group">
                            {{-- Corregido: Se usa el objeto $doctor directamente para que Laravel detecte el parámetro 'medico' --}}
                            <a href="{{ route('admin.doctors.edit', $doctor) }}" class="btn btn-sm btn-outline-primary" title="Editar perfil">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
