@extends('layouts.admin')

@section('header', 'Cuerpo Médico')

@section('header-actions')
    <a href="{{ route('doctors.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus-fill me-2"></i>Nuevo Doctor
    </a>
@endsection

@section('content')
<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Doctor</th>
                    <th>RUT / SIS</th>
                    <th>Especialidades</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($doctors as $doctor)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                {{ substr($doctor->user->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="fw-bold">{{ $doctor->user->name }}</div>
                                <div class="text-muted small">{{ $doctor->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div>{{ $doctor->rut }}</div>
                        <div class="badge bg-light text-dark border">{{ $doctor->rnpi_number ?? 'Sin RNPI' }}</div>
                    </td>
                    <td>
                        @foreach($doctor->specialties as $esp)
                            <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $esp->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        @if($doctor->is_active)
                            <span class="badge bg-success-subtle text-success">Activo</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
