<div>
    {{-- Sección de Filtros --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="small fw-bold text-muted text-uppercase d-flex justify-content-between">
                        Paciente (Nombre o RUT)
                        <span wire:loading wire:target="searchPatient" class="spinner-border spinner-border-sm text-primary"></span>
                    </label>
                    <input wire:model.live.debounce.300ms="searchPatient" type="text" class="form-control form-control-sm" placeholder="Buscar paciente...">
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold text-muted text-uppercase text-truncate">Médico Responsable</label>
                    <select wire:model.live="doctorId" class="form-select form-select-sm">
                        <option value="">Todos los médicos</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->id }}">{{ $doc->prefix }} {{ $doc->user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold text-muted text-uppercase">Desde</label>
                    <input wire:model.live="dateFrom" type="date" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold text-muted text-uppercase">Hasta</label>
                    <input wire:model.live="dateTo" type="date" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button wire:click="clearFilters" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-eraser me-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Resultados --}}
    <div class="card shadow-sm border-0" wire:loading.class="opacity-50">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3">Fecha / Hora</th>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Examen / Tipo</th>
                            <th>Estado Pago / Firma</th>
                            <th class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td class="ps-3 small">
                                <span class="d-block fw-bold text-dark">{{ $order->created_at->format('d/m/Y') }}</span>
                                <span class="text-muted">{{ $order->created_at->format('H:i') }} hrs</span>
                            </td>
                            <td>
                                @if($order->patient)
                                    <div class="fw-bold text-dark text-uppercase small">{{ $order->patient->full_name }}</div>
                                    <small class="text-muted font-monospace">{{ $order->patient->rut ?? 'Sin RUT' }}</small>
                                @else
                                    <span class="text-danger small italic">
                                        <i class="bi bi-person-x me-1"></i> Cuenta Eliminada
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($order->doctor)
                                    <span class="text-dark small">{{ $order->doctor->prefix }} {{ $order->doctor->user->name }}</span>
                                @else
                                    <span class="badge bg-light text-warning border border-warning fw-normal">PTE. TOMA</span>
                                @endif
                            </td>
                            <td>
                                <div class="small text-dark fw-medium">{{ Str::limit($order->display_name, 35) }}</div>
                                <small class="text-muted text-uppercase" style="font-size: 0.65rem;">
                                    {{ $order->type === 'custom' ? 'Personalizada' : 'Estándar' }}
                                </small>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    {{-- Lógica de Colores para Estados de Pago --}}
                                    @php
                                        $statusConfig = [
                                            'paid'           => ['class' => 'bg-primary', 'label' => 'PAGADA'],
                                            'refund_pending' => ['class' => 'bg-warning text-dark', 'label' => 'REEMBOLSO PTE'],
                                            'refunded'       => ['class' => 'bg-dark', 'label' => 'REEMBOLSADA'],
                                            'rejected'       => ['class' => 'bg-danger', 'label' => 'RECHAZADA'],
                                        ];
                                        $currentStatus = $statusConfig[$order->status] ?? ['class' => 'bg-secondary', 'label' => strtoupper($order->status)];
                                    @endphp

                                    <span class="badge {{ $currentStatus['class'] }}" style="font-size: 0.6rem; width: fit-content;">
                                        {{ $currentStatus['label'] }}
                                    </span>

                                    {{-- Estado de Firma --}}
                                    @php $isSigned = $order->activePrescription && $order->activePrescription->status === 'signed'; @endphp
                                    <span class="badge {{ $isSigned ? 'bg-success' : 'bg-light text-muted border' }}" style="font-size: 0.6rem; width: fit-content;">
                                        {{ $isSigned ? 'FIRMADA' : 'PTE. FIRMA' }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="bi bi-search me-1"></i> Auditar
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-search fs-2 d-block mb-2"></i>
                                No se encontraron órdenes bajo estos criterios.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            {{ $orders->links() }}
        </div>
    </div>
</div>
