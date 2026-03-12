@extends('layouts.admin')

@section('header')
    {{ auth()->user()->hasRole('doctor') ? 'Panel de Órdenes Médicas' : 'Gestión Global de Órdenes' }}
@endsection

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0 text-dark fw-bold">
                    <i class="bi bi-file-earmark-text text-primary me-2"></i> Listado de Órdenes
                </h5>
            </div>
            @role('admin|director_tecnico')
            <div class="col text-end">
                <button class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-download me-1"></i> Exportar
                </button>
            </div>
            @endrole
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Paciente</th>
                        <th>Examen / Requerimiento</th>
                        @role('admin|director_tecnico')
                        <th>Doctor Asignado</th>
                        @endrole
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div>
                                    <div class="fw-bold text-dark">
                                        {{ $order->patient->full_name }}
                                        @if($order->patient->trashed())
                                            <i class="bi bi-archive text-muted ms-1" title="Paciente eliminado del círculo activo"></i>
                                        @endif
                                    </div>
                                    <small class="text-muted">RUT: {{ $order->patient->rut }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($order->examType)
                                <span class="badge bg-info-subtle text-info border border-info-subtle">
                                    {{ $order->examType->name }}
                                </span>
                            @else
                                <div class="d-flex flex-column">
                                    <span class="badge bg-purple-subtle text-purple border border-purple-subtle mb-1" style="background-color: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; align-self: start;">
                                        <i class="bi bi-stars me-1"></i> Solicitud Especial
                                    </span>
                                    <small class="text-muted text-truncate" style="max-width: 200px;" title="{{ $order->custom_description }}">
                                        {{ Str::limit($order->custom_description, 40) }}
                                    </small>
                                </div>
                            @endif
                        </td>
                        @role('admin|director_tecnico')
                        <td>
                            @if($order->doctor && $order->doctor->user)
                                <div class="small text-dark fw-medium">Dr. {{ $order->doctor->user->name }}</div>
                                <small class="text-muted">{{ $order->doctor->specialty->name ?? '' }}</small>
                            @else
                                <span class="text-muted small fst-italic">Sin asignar</span>
                            @endif
                        </td>
                        @endrole
                        <td>
                            <div class="small">{{ $order->created_at->format('d/m/Y') }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">{{ $order->created_at->format('H:i') }} hrs</div>
                        </td>
                        <td>
                            @php
                                $statusBadge = [
                                    'pending' => 'bg-warning text-dark',
                                    'signed' => 'bg-success text-white',
                                    'rejected' => 'bg-danger text-white',
                                    'cancelled' => 'bg-secondary text-white'
                                ];
                                $statusIcon = [
                                    'pending' => 'bi-clock-history',
                                    'signed' => 'bi-check-all',
                                    'rejected' => 'bi-x-circle',
                                    'cancelled' => 'bi-slash-circle'
                                ];
                                $currentStatus = $order->status;
                            @endphp
                            <span class="badge {{ $statusBadge[$currentStatus] ?? 'bg-secondary' }} px-2 py-1">
                                <i class="bi {{ $statusIcon[$currentStatus] ?? 'bi-info-circle' }} me-1"></i>
                                {{ ucfirst(__($currentStatus)) }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                {{-- Lógica de Doctor --}}
                                @if(auth()->user()->hasRole('doctor'))
                                    @if($order->status == 'pending')
                                        <a href="{{ route('admin.orders.sign.form', $order->id) }}" class="btn btn-sm btn-primary shadow-sm">
                                            <i class="bi bi-vector-pen me-1"></i> Firmar
                                        </a>
                                    @elseif($order->status == 'signed')
                                        <a href="#" class="btn btn-sm btn-outline-success border-2 shadow-sm" title="Descargar Orden Médica">
                                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> PDF
                                        </a>
                                    @endif
                                @endif

                                {{-- Acciones Generales --}}
                                <a href="#" class="btn btn-sm btn-outline-dark ms-1" title="Ver detalles y trazabilidad">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        @php $cols = auth()->user()->hasAnyRole('admin', 'director_tecnico') ? 6 : 5; @endphp
                        <td colspan="{{ $cols }}" class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                            <span class="text-muted fw-medium">No se encontraron órdenes en esta sección.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($orders->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
