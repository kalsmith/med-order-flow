<div wire:poll.15s class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
    <div class="card-header bg-white pt-4 pb-0 border-bottom-0">
        <div class="d-flex justify-content-between align-items-center mb-3 px-2">
            <h5 class="mb-0 text-dark fw-bold">
                <i class="bi bi-clipboard2-pulse text-primary me-2"></i>
                Gestión de Órdenes
            </h5>
            <div class="text-muted small">
                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle fw-medium">
                    <i class="bi bi-dot fs-6"></i> En vivo
                </span>
            </div>
        </div>

        {{-- Pestañas de Navegación --}}
        <ul class="nav nav-tabs border-bottom-0 px-2">
            <li class="nav-item">
                <button wire:click="setTab('available')"
                    class="nav-link {{ $tab === 'available' ? 'active fw-bold border-bottom border-primary border-3' : 'text-muted' }} border-0 bg-transparent pb-3">
                    Pendientes de Firma
                </button>
            </li>
            <li class="nav-item">
                <button wire:click="setTab('signed')"
                    class="nav-link {{ $tab === 'signed' ? 'active fw-bold border-bottom border-primary border-3' : 'text-muted' }} border-0 bg-transparent pb-3">
                    Historial de Firmas
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light-subtle">
                    <tr>
                        <th class="ps-4 py-3 text-uppercase small fw-bold text-muted" style="font-size: 0.75rem;">Paciente</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted" style="font-size: 0.75rem;">Examen / Tipo</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted" style="font-size: 0.75rem;">Fecha</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted" style="font-size: 0.75rem;">Estado</th>
                        <th class="text-end pe-4 py-3 text-uppercase small fw-bold text-muted" style="font-size: 0.75rem;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $myDoctorId = auth()->user()->doctor->id ?? null;
                    @endphp

                    @forelse($orders as $order)
                        @php
                            // Casting manual para evitar errores de tipo int/string
                            $orderDocId = $order->doctor_id ? strval($order->doctor_id) : null;
                            $meId = strval($myDoctorId);

                            // Lógica de estados
                            $isClaimedByOther = $orderDocId &&
                                               $orderDocId !== $meId &&
                                               $order->claimed_at &&
                                               $order->claimed_at->gt(now()->subMinutes(20));

                            $isClaimedByMe = $orderDocId === $meId && $order->status === 'paid';
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $order->patient->full_name ?? 'N/A' }}</div>
                                <div class="text-muted small">{{ $order->patient->rut ?? '' }}</div>
                            </td>
                            <td>
                                @if($order->type === 'custom')
                                    <span class="badge border" style="background-color: #f3e8ff; color: #6b21a8; border-color: #d8b4fe;">
                                        <i class="bi bi-stars me-1"></i> Especial (Custom)
                                    </span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-medium">
                                        {{ $order->examType->name ?? 'Estándar' }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium text-dark" style="font-size: 0.85rem;">{{ $order->created_at->format('d/m/Y') }}</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">{{ $order->created_at->format('H:i') }} hrs</div>
                            </td>
                            <td>
                                @if($order->status === 'signed')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-medium">
                                        <i class="bi bi-patch-check-fill me-1"></i> Firmado
                                    </span>
                                @elseif($isClaimedByOther)
                                    <span class="badge bg-light text-muted border border-secondary-subtle fw-normal">
                                        <i class="bi bi-person-fill-lock me-1"></i> En revisión
                                    </span>
                                @elseif($isClaimedByMe)
                                    <span class="badge bg-info-subtle text-info border border-info-subtle fw-medium">
                                        <i class="bi bi-arrow-right-circle-fill me-1"></i> En tu poder
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-medium">
                                        Pendiente
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($order->status !== 'signed')
                                    @if($isClaimedByOther)
                                        <button class="btn btn-sm btn-light border text-muted" disabled title="Bloqueado por otro profesional">
                                            <i class="bi bi-lock-fill"></i>
                                        </button>
                                    @else
                                        <a href="{{ route('admin.orders.sign.form', ['order' => $order->id]) }}"
                                           class="btn btn-sm {{ $isClaimedByMe ? 'btn-info text-white' : 'btn-primary' }} px-3 rounded-pill shadow-sm fw-bold">
                                            <i class="bi {{ $isClaimedByMe ? 'bi-play-fill' : 'bi-vector-pen' }} me-1"></i>
                                            {{ $isClaimedByMe ? 'Continuar' : 'Atender' }}
                                        </a>
                                    @endif
                                @else
                                    <div class="btn-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                                        {{-- Ver Detalles --}}
                                        <a href="{{ route('admin.orders.sign.form', ['order' => $order->id]) }}"
                                           class="btn btn-sm btn-white border border-end-0" title="Ver Detalles">
                                            <i class="bi bi-eye text-primary"></i>
                                        </a>
                                        {{-- Descargar PDF --}}
                                        <a href="{{ route('admin.orders.pdf', ['order' => $order->id]) }}"
                                           target="_blank"
                                           class="btn btn-sm btn-white border" title="Descargar PDF">
                                            <i class="bi bi-file-pdf text-danger"></i>
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 bg-light-subtle">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-3 opacity-50"></i>
                                <h6 class="text-muted fw-normal">No hay registros en esta sección.</h6>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-center">
        {{ $orders->links() }}
    </div>
</div>

<style>
    .btn-white { background-color: #fff; color: #374151; transition: all 0.2s; }
    .btn-white:hover { background-color: #f9fafb; color: #000; }
    .nav-tabs .nav-link.active { color: #0d6efd !important; border-bottom: 3px solid #0d6efd !important; }
    .table-hover tbody tr:hover { background-color: #f8fafc; }
    .badge { font-size: 0.75rem; padding: 0.4em 0.8em; }
</style>
