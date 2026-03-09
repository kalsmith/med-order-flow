@extends('layouts.admin')

@section('header')
    {{ auth()->user()->hasRole('doctor') ? 'Mis Órdenes Pendientes' : 'Gestión Global de Órdenes' }}
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
                        <th>Examen</th>
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
                            <div class="fw-bold text-dark">{{ $order->patient->user->name ?? 'N/A' }}</div>
                            <small class="text-muted">RUT: {{ $order->patient->rut }}</small>
                        </td>
                        <td>
                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                {{ $order->examType->name }}
                            </span>
                        </td>
                        @role('admin|director_tecnico')
                        <td>
                            <div class="small">Dr. {{ $order->doctor->user->name ?? 'Sin asignar' }}</div>
                        </td>
                        @endrole
                        <td>
                            <div class="small">{{ $order->created_at->format('d/m/Y') }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">{{ $order->created_at->format('H:i') }} hrs</div>
                        </td>
                        <td>
                            @if($order->status == 'pending')
                                <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i> Pendiente</span>
                            @elseif($order->status == 'signed')
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Firmada</span>
                            @else
                                <span class="badge bg-secondary">{{ $order->status }}</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            @if(auth()->user()->hasRole('doctor') && $order->status == 'pending')
<a href="{{ route('admin.orders.sign.form', $order->id) }}" class="btn btn-sm btn-primary">
    <i class="bi bi-vector-pen"></i> Firmar
</a>
                            @endif
                            <a href="#" class="btn btn-sm btn-outline-dark">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            No se encontraron órdenes registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $orders->links() }}
    </div>
</div>
@endsection
