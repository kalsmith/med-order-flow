<div>
    {{-- Sección de Filtros --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="small fw-bold text-muted text-uppercase">Paciente (Nombre o RUT)</label>
                    <input wire:model.debounce.400ms="searchPatient" type="text" class="form-control form-control-sm" placeholder="Buscar...">
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold text-muted text-uppercase">Médico Responsable</label>
                    <select wire:model="doctorId" class="form-select form-select-sm">
                        <option value="">Todos los médicos</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->id }}">{{ $doc->prefix }} {{ $doc->user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold text-muted text-uppercase">Desde</label>
                    <input wire:model="dateFrom" type="date" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold text-muted text-uppercase">Hasta</label>
                    <input wire:model="dateTo" type="date" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button wire:click="$set('searchPatient', ''); $set('doctorId', ''); $set('dateFrom', ''); $set('dateTo', '');"
                            class="btn btn-sm btn-light border w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Resultados --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3">Fecha / Hora</th>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Examen</th>
                            <th>Estado</th>
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
                                    <div class="fw-bold text-dark">{{ $order->patient->full_name }}</div>
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
                                <div class="small text-dark fw-medium">{{ Str::limit($order->display_name, 30) }}</div>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <span class="badge {{ $order->status == 'paid' ? 'bg-primary' : 'bg-secondary' }}" style="font-size: 0.6rem; width: fit-content;">
                                        {{ strtoupper($order->status) }}
                                    </span>
                                    @php $isSigned = $order->activePrescription && $order->activePrescription->status === 'signed'; @endphp
                                    <span class="badge {{ $isSigned ? 'bg-success' : 'bg-warning text-dark' }}" style="font-size: 0.6rem; width: fit-content;">
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
                                No se encontraron órdenes con los filtros aplicados.
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
