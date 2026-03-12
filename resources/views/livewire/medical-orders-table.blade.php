<div wire:poll.10s class="card shadow-sm border-0">
    <div class="card-header bg-white pt-3 pb-0 border-bottom-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 text-dark fw-bold">
                <i class="bi bi-clipboard2-pulse text-primary me-2"></i>
                Gestión de Órdenes
            </h5>
            <div class="text-muted small">
                <i class="bi bi-dot text-success fs-4"></i> En vivo
            </div>
        </div>

        {{-- Pestañas --}}
        <ul class="nav nav-tabs border-bottom-0">
            <li class="nav-item">
                <button wire:click="setTab('available')"
                    class="nav-link {{ $tab === 'available' ? 'active fw-bold border-bottom border-primary border-3' : 'text-muted' }} border-0 bg-transparent pb-3">
                    Disponibles para Firma
                </button>
            </li>
            <li class="nav-item">
                <button wire:click="setTab('signed')"
                    class="nav-link {{ $tab === 'signed' ? 'active fw-bold border-bottom border-primary border-3' : 'text-muted' }} border-0 bg-transparent pb-3">
                    Mis Órdenes Firmadas
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-uppercase small fw-bold text-muted">Paciente</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted">Examen</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted">Fecha</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted">Estado</th>
                        <th class="text-end pe-4 py-3 text-uppercase small fw-bold text-muted">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $myDoctorId = auth()->user()->doctor->id ?? null;
                            $isClaimedByOther = $order->doctor_id && $order->doctor_id !== $myDoctorId && $order->claimed_at > now()->subMinutes(20);
                            $isClaimedByMe = $order->doctor_id === $myDoctorId && $order->status === 'paid';
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $order->patient->full_name }}</div>
                                <small class="text-muted">{{ $order->patient->rut }}</small>
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
                            <td>
                                <div class="small fw-medium">{{ $order->created_at->format('d/m/Y') }}</div>
                                <div class="text-muted small">{{ $order->created_at->format('H:i') }} hrs</div>
                            </td>
                            <td>
                                @if($tab === 'available')
                                    @if($isClaimedByOther)
                                        <span class="badge bg-light text-muted border border-secondary-subtle">
                                            <i class="bi bi-person-fill-lock me-1"></i> Siendo revisada
                                        </span>
                                    @else
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">
                                            Lista para firmar
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-patch-check-fill me-1"></i> Firmada por mí
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($tab === 'available')
                                    @if($isClaimedByOther)
                                        <button class="btn btn-sm btn-light border" disabled title="Otro médico está trabajando en esto">
                                            <i class="bi bi-lock-fill"></i>
                                        </button>
                                    @else
                                        <a href="{{ route('admin.orders.sign.form', $order->id) }}"
                                           class="btn btn-sm {{ $isClaimedByMe ? 'btn-info text-white' : 'btn-primary' }} px-3 shadow-sm">
                                            <i class="bi {{ $isClaimedByMe ? 'bi-play-fill' : 'bi-vector-pen' }} me-1"></i>
                                            {{ $isClaimedByMe ? 'Continuar' : 'Firmar' }}
                                        </a>
                                    @endif
                                @else
                                    {{-- Acciones para firmadas --}}
                                    <div class="btn-group">
                                        <a href="#" class="btn btn-sm btn-outline-success"><i class="bi bi-file-pdf"></i></a>
                                        <a href="#" class="btn btn-sm btn-light border"><i class="bi bi-eye"></i></a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 bg-light-subtle">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    {{ $tab === 'available' ? 'No hay órdenes pendientes de firma.' : 'Aún no has firmado ninguna orden.' }}
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-top-0 py-3">
        {{ $orders->links() }}
    </div>
</div>
