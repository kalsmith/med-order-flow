@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4 text-dark fw-bold">
        <i class="bi bi-shield-check me-2 text-primary"></i>Supervisión de Órdenes Médicas (DT)
    </h2>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3">Fecha / Hora</th>
                            <th>Paciente</th>
                            <th>Médico Responsable</th>
                            <th>Examen / Tipo</th>
                            <th>Estado Pago / Firma</th>
                            <th class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td class="ps-3 small">
                                <span class="d-block fw-bold text-dark">{{ $order->created_at->format('d/m/Y') }}</span>
                                <span class="text-muted">{{ $order->created_at->format('H:i') }} hrs</span>
                            </td>
                            <td>
                                @if($order->patient)
                                    <div class="fw-bold text-dark">{{ $order->patient->full_name }}</div>
                                    <small class="text-muted font-monospace">{{ $order->patient->rut ?? 'Sin RUT' }}</small>
                                @else
                                    <span class="text-danger small italic">
                                        <i class="bi bi-person-x me-1"></i> El usuario borró su cuenta
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($order->doctor)
                                    <span class="text-dark">{{ $order->doctor->prefix }} {{ $order->doctor->user->name ?? 'Médico' }}</span>
                                @else
                                    <span class="badge bg-light text-warning border border-warning fw-normal">
                                        <i class="bi bi-clock-history me-1"></i> Pendiente
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="small text-dark fw-medium">{{ Str::limit($order->display_name, 35) }}</div>
                                <small class="badge bg-light text-muted border fw-normal" style="font-size: 0.65rem;">
                                    {{ $order->type === 'custom' ? 'PERSONALIZADA' : 'ESTÁNDAR' }}
                                </small>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1" style="width: fit-content;">
                                    {{-- Estado de Pago --}}
                                    <span class="badge {{ $order->status == 'paid' ? 'bg-primary' : 'bg-secondary' }}" style="font-size: 0.6rem;">
                                        {{ strtoupper($order->status) }}
                                    </span>

                                    {{-- Estado de Firma --}}
                                    @if($order->activePrescription && $order->activePrescription->status === 'signed')
                                        <span class="badge bg-success" style="font-size: 0.6rem;">FIRMADA</span>
                                    @else
                                        <span class="badge bg-warning text-dark" style="font-size: 0.6rem;">PTE. FIRMA</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="bi bi-search me-1"></i> Auditar
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
