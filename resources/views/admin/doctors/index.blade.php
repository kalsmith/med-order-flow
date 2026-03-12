@extends('layouts.admin')

@section('header', 'Cuerpo Médico')

@section('header-actions')
    <a href="{{ route('admin.doctors.create') }}" class="btn btn-primary shadow-sm px-3">
        <i class="bi bi-person-plus-fill me-2"></i>Nuevo Profesional
    </a>
@endsection

@section('content')

{{-- Alertas de estado mejoradas --}}
@if (session('status') || session('success'))
    <div class="alert alert-success border-0 shadow-sm mb-4 alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="bi bi-check-circle-fill me-3 fs-4"></i>
        <div>
            {{ session('status') ?? session('success') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card shadow-sm border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4 py-3 border-0 text-uppercase small fw-bold text-muted">Doctor</th>
                    <th class="py-3 border-0 text-uppercase small fw-bold text-muted">Identificación</th>
                    <th class="py-3 border-0 text-uppercase small fw-bold text-muted">Especialidad</th>
                    <th class="py-3 border-0 text-center text-uppercase small fw-bold text-muted">Sello/Firma</th>
                    <th class="py-3 border-0 text-uppercase small fw-bold text-muted">Estado</th>
                    <th class="py-3 border-0 text-end pe-4 text-uppercase small fw-bold text-muted">Acciones</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @foreach($doctors as $doctor)
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-3">
                                {{ strtoupper(substr($doctor->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-bold text-dark mb-0">{{ $doctor->user->name }}</div>
                                <div class="text-muted small">{{ $doctor->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="text-dark small fw-medium">{{ $doctor->rut }}</div>
                        <div class="text-primary small font-monospace" style="font-size: 0.7rem;">
                            RNPI: {{ $doctor->rnpi_number ?? 'Pendiente' }}
                        </div>
                    </td>
                    <td>
                        {{-- Soporte para relación individual (specialty) o múltiple (specialties) --}}
                        @if($doctor->specialty)
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                {{ $doctor->specialty->name }}
                            </span>
                        @else
                            <div class="d-flex flex-wrap gap-1">
                                @forelse($doctor->specialties ?? [] as $esp)
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">
                                        {{ $esp->name }}
                                    </span>
                                @empty
                                    <span class="text-muted small fst-italic">No asignada</span>
                                @endforelse
                            </div>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($doctor->signature_path)
                            <div class="d-flex flex-column align-items-center" title="Firma Digitalizada" data-bs-toggle="tooltip">
                                <i class="bi bi-shield-check text-success fs-5"></i>
                                <span class="text-success" style="font-size: 0.6rem; font-weight: bold;">CARGADA</span>
                            </div>
                        @else
                            <div class="d-flex flex-column align-items-center" title="Requiere subir firma para emitir órdenes" data-bs-toggle="tooltip">
                                <i class="bi bi-shield-exclamation text-warning fs-5"></i>
                                <span class="text-warning" style="font-size: 0.6rem; font-weight: bold;">FALTANTE</span>
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($doctor->is_active)
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3">
                                Activo
                            </span>
                        @else
                            <span class="badge bg-light text-muted border px-3">
                                Inactivo
                            </span>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group shadow-sm">
                            <a href="{{ route('admin.doctors.edit', $doctor) }}" class="btn btn-sm btn-white border" title="Editar Perfil">
                                <i class="bi bi-pencil-square text-primary me-1"></i> Editar
                            </a>
                            {{-- Botón adicional por si quieres ver su panel rápidamente --}}
                            <button type="button" class="btn btn-sm btn-white border dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">Opciones</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><a class="dropdown-item small" href="#"><i class="bi bi-eye me-2"></i>Ver Historial</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="#" method="POST" onsubmit="return confirm('¿Inactivar este médico?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dropdown-item small text-danger">
                                            <i class="bi bi-person-x me-2"></i>Desactivar
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($doctors->hasPages())
    <div class="card-footer bg-white border-top-0 py-3">
        {{ $doctors->links() }}
    </div>
    @endif
</div>

<style>
    .avatar-circle {
        width: 38px;
        height: 38px;
        background-color: #eef2ff;
        color: #4f46e5;
        border: 1px solid #e0e7ff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
    }
    .btn-white {
        background-color: #fff;
        color: #374151;
    }
    .btn-white:hover {
        background-color: #f9fafb;
    }
    .table-hover tbody tr:hover {
        background-color: #fbfcfe;
    }
</style>

@endsection
