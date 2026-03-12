<div wire:poll.5s class="card shadow-sm border-0 overflow-hidden">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0 text-dark fw-bold">
                    <i class="bi bi-file-earmark-medical text-primary me-2"></i>
                    {{ auth()->user()->hasRole('doctor') ? 'Órdenes Disponibles para Firma' : 'Listado General' }}
                </h5>
            </div>
            <div class="col text-end">
                @role('doctor')
                    <span class="badge bg-light text-muted border py-2 px-3">
                        <i class="bi bi-info-circle me-1"></i> Actualizado en vivo cada 5s
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
                        <th class="py-3 text-uppercase small fw-bold text-muted">Requerimiento</th>
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
                        @php
                            $myDoctorId = auth()->user()->doctor->id ?? null;
                            $isClaimedByOther = $order->doctor_id &&
                                                $order->doctor_id !== $myDoctorId &&
                                                in_array($order->status, ['pending', 'paid']) &&
                                                $order->claimed_at &&
                                                $order->claimed_at > now()->subMinutes(20);

                            $isClaimedByMe = $order->doctor_id && $order->doctor_id === $myDoctorId &&
                                             in_array($order->status, ['pending', 'paid']);
                        @endphp
                        <tr class="{{ $isClaimedByMe ? 'table-info-subtle' : '' }} {{ $isClaimedByOther ? 'opacity-75' : '' }}">
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $order->patient->full_name }}</div>
                                <small class="text-muted">RUT: {{ $order->patient->rut }}</small>
                            </td>
                            <td>
                                @if($order->examType)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2">
                                        {{ $order->examType->name }}
                                    </span>
                                @else
                                    <span class="badge bg-purple-subtle text-purple border border-purple-subtle">
                                        <i class="bi bi-stars me-1"></i> Especial
                                    </span>
                                @endif
                            </td>
                            @role('admin|director_tecnico')
                            <td>
                                @if($order->doctor)
                                    <div class="small"><i class="bi bi-person-badge me-1"></i> Dr. {{ $order->doctor->user->name }}</div>
                                @else
                                    <span class="text-muted small">Sin asignar</span>
                                @endif
                            </td>
                            @endrole
                            <td>
                                <div class="small fw-medium">{{ $order->created_at->format('d/m/Y') }}</div>
                                <div class="text-muted small">{{ $order->created_at->format('H:i') }} hrs</div>
                            </td>
                            <td>
                                @if($isClaimedByOther)
                                    <span class="badge bg-light text-muted border border-secondary-subtle">
                                        <i class="bi bi-person-fill-lock me-1"></i> En revisión
                                    </span>
                                @else
                                    @php
                                        $badges = [
                                            'paid' => 'bg-info-subtle text-info-emphasis border-info-subtle',
                                            'signed' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                                            'rejected' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle'
                                        ];
                                    @endphp
                                    <span class="badge border {{ $badges[$order->status] ?? 'bg-light' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($isClaimedByOther)
                                    <button class="btn btn-sm btn-light border" disabled>
                                        <i class="bi bi-lock-fill"></i> Ocupada
                                    </button>
                                @else
                                    <div class="btn-group">
                                        @if($order->status == 'paid' || $isClaimedByMe)
                                            <a href="{{ route('admin.orders.sign.form', $order->id) }}"
                                               class="btn btn-sm {{ $isClaimedByMe ? 'btn-info text-white' : 'btn-primary' }}">
                                                {{ $isClaimedByMe ? 'Continuar' : 'Firmar' }}
                                            </a>
                                        @endif
                                        <a href="#" class="btn btn-sm btn-white border ms-1"><i class="bi bi-eye"></i></a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5">No hay órdenes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-3">
        {{ $orders->links() }}
    </div>

    <style>
        .table-info-subtle { background-color: rgba(13, 202, 240, 0.05); }
        .bg-purple-subtle { background-color: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; }
        .text-purple { color: #7e22ce; }
        .btn-white { background-color: #fff; }
    </style>
</div>
