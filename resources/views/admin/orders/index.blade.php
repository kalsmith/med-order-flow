@extends('layouts.admin')

@section('header')
    {{ auth()->user()->hasRole('doctor') ? 'Panel de Órdenes Médicas' : 'Gestión Global de Órdenes' }}
@endsection

@section('content')
<div class="card shadow-sm border-0 overflow-hidden">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0 text-dark fw-bold">
                    <i class="bi bi-file-earmark-medical text-primary me-2"></i>
                    {{ auth()->user()->hasRole('doctor') ? 'Órdenes Disponibles para Firma' : 'Listado General' }}
                </h5>
            </div>
            <div class="col text-end">
                @role('admin|director_tecnico')
                    <button class="btn btn-outline-secondary btn-sm shadow-sm">
                        <i class="bi bi-download me-1"></i> Exportar
                    </button>
                @endrole
                @role('doctor')
                    <span class="badge bg-light text-muted border py-2 px-3">
                        <i class="bi bi-info-circle me-1"></i> Las órdenes se liberan tras 20 min de inactividad
                    </span>
                @endrole
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-uppercase small fw-bold text-muted">Paciente</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted">Examen / Requerimiento</th>
                        @role('admin|director_tecnico')
                            <th class="py-3 text-uppercase small fw-bold text-muted">Doctor Asignado</th>
                        @endrole
                        <th class="py-3 text-uppercase small fw-bold text-muted">Fecha</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted">Estado</th>
                        <th class="text-end pe-4 py-3 text-uppercase small fw-bold text-muted">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div>
                                    <div class="fw-bold text-dark mb-0">
                                        {{ $order->patient->full_name }}
                                        @if($order->patient->trashed())
                                            <i class="bi bi-archive text-muted ms-1" title="Paciente en archivo histórico"></i>
                                        @endif
                                    </div>
                                    <small class="text-muted">RUT: {{ $order->patient->rut }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($order->examType)
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2">
                                        {{ $order->examType->name }}
                                    </span>
                                </div>
                            @else
                                <div class="d-flex flex-column">
                                    <span class="badge bg-purple-subtle text-purple border border-purple-subtle mb-1" style="background-color: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; align-self: start;">
                                        <i class="bi bi-stars me-1"></i> Solicitud Especial
                                    </span>
                                    <small class="text-muted text-truncate" style="max-width: 180px;" title="{{ $order->custom_description }}">
                                        {{ Str::limit($order->custom_description, 35) }}
                                    </small>
                                </div>
                            @endif
                        </td>
                        @role('admin|director_tecnico')
                        <td>
                            @if($order->doctor && $order->doctor->user)
                                <div class="d-flex align-items-center text-dark small fw-medium">
                                    <i class="bi bi-person-badge me-2 text-primary"></i>
                                    Dr. {{ $order->doctor->user->name }}
                                </div>
                            @else
                                <span class="text-muted small fst-italic">No asignada</span>
                            @endif
                        </td>
                        @endrole
                        <td>
                            <div class="small fw-medium">{{ $order->created_at->format('d/m/Y') }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">{{ $order->created_at->format('H:i') }} hrs</div>
                        </td>
                        <td>
                            @php
                                $statusBadge = [
                                    'pending' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                    'signed' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                                    'rejected' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
                                    'cancelled' => 'bg-light text-muted border-light-subtle'
                                ];
                                $statusIcon = [
                                    'pending' => 'bi-clock-history',
                                    'signed' => 'bi-patch-check-fill',
                                    'rejected' => 'bi-x-circle',
                                    'cancelled' => 'bi-slash-circle'
                                ];
                                $currentStatus = $order->status;
                            @endphp
                            <span class="badge border {{ $statusBadge[$currentStatus] ?? 'bg-secondary' }} px-2 py-1">
                                <i class="bi {{ $statusIcon[$currentStatus] ?? 'bi-info-circle' }} me-1"></i>
                                {{ ucfirst(__($currentStatus)) }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group shadow-sm">
                                {{-- Lógica para Doctores --}}
                                @if(auth()->user()->hasRole('doctor'))
                                    @if($order->status == 'pending')
                                        @if($order->doctor_id && $order->doctor_id !== auth()->user()->doctor->id)
                                            <button class="btn btn-sm btn-light text-muted border" disabled title="Siendo revisada por otro médico">
                                                <i class="bi bi-lock-fill me-1"></i> En revisión
                                            </button>
                                        @else
                                            <a href="{{ route('admin.orders.sign.form', $order->id) }}" class="btn btn-sm btn-primary px-3">
                                                <i class="bi bi-vector-pen me-1"></i> Firmar
                                            </a>
                                        @endif
                                    @elseif($order->status == 'signed')
                                        <a href="#" class="btn btn-sm btn-outline-success border" title="Ver PDF">
                                            <i class="bi bi-file-earmark-pdf"></i> PDF
                                        </a>
                                    @endif
                                @endif

                                {{-- Acciones Generales --}}
                                <a href="#" class="btn btn-sm btn-white border ms-1" title="Ver Detalles">
                                    <i class="bi bi-eye text-dark"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        @php $cols = auth()->user()->hasAnyRole('admin', 'director_tecnico') ? 6 : 5; @endphp
                        <td colspan="{{ $cols }}" class="text-center py-5 bg-light-subtle">
                            <i class="bi bi-clipboard2-x fs-1 text-muted d-block mb-3"></i>
                            <span class="text-muted fw-medium">No hay órdenes pendientes de tu especialidad en este momento.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($orders->hasPages())
        <div class="card-footer bg-white py-3 border-top">
            {{ $orders->links() }}
        </div>
    @endif
</div>

<style>
    .bg-purple-subtle { background-color: #f3e8ff; }
    .text-purple { color: #7e22ce; }
    .btn-white { background-color: #fff; }
</style>
@endsection
