<div wire:poll.10s class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
    <div class="card-header bg-white pt-4 pb-0 border-bottom-0">
        <div class="d-flex justify-content-between align-items-center mb-3 px-2">
            <h5 class="mb-0 text-dark fw-bold">
                <i class="bi bi-clipboard2-pulse text-primary me-2"></i>
                Gestión de Órdenes
            </h5>
            <div class="text-muted small d-flex align-items-center">
                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle fw-medium">
                    <i class="bi bi-dot fs-6"></i> En vivo
                </span>
            </div>
        </div>

        {{-- Pestañas --}}
        <ul class="nav nav-tabs border-bottom-0 px-2">
            <li class="nav-item">
                <button wire:click="setTab('available')"
                    class="nav-link {{ $tab === 'available' ? 'active fw-bold border-bottom border-primary border-3' : 'text-muted' }} border-0 bg-transparent pb-3 position-relative">
                    Disponibles para Firma
                </button>
            </li>
            <li class="nav-item">
                <button wire:click="setTab('signed')"
                    class="nav-link {{ $tab === 'signed' ? 'active fw-bold border-bottom border-primary border-3' : 'text-muted' }} border-0 bg-transparent pb-3">
                    Mis Firmas (Manual)
                </button>
            </li>
            <li class="nav-item">
                <button wire:click="setTab('auto_signed')"
                    class="nav-link {{ $tab === 'auto_signed' ? 'active fw-bold border-bottom border-primary border-3' : 'text-muted' }} border-0 bg-transparent pb-3">
                    <i class="bi bi-robot me-1"></i> Firma Automática
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
                        <th class="py-3 text-uppercase small fw-bold text-muted" style="font-size: 0.75rem;">Examen / Detalle</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted" style="font-size: 0.75rem;">Fecha</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted" style="font-size: 0.75rem;">Estado</th>
                        <th class="text-end pe-4 py-3 text-uppercase small fw-bold text-muted" style="font-size: 0.75rem;">Acciones</th>
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
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3">
                                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 0.85rem;">
                                            {{ substr($order->patient->full_name, 0, 1) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $order->patient->full_name }}</div>
                                        <div class="text-muted small">{{ $order->patient->rut }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($order->type === 'custom')
                                    {{-- Corregido: Morado con alto contraste --}}
                                    <div class="d-flex flex-column">
                                        <span class="badge border border-purple text-purple mb-1" style="background-color: #f3e8ff; color: #6b21a8; border-color: #d8b4fe !important; width: fit-content;">
                                            <i class="bi bi-stars me-1"></i> Solicitud Especial
                                        </span>
                                        <small class="text-dark fw-medium text-truncate" style="max-width: 180px;" title="{{ $order->custom_description }}">
                                            {{ $order->custom_description }}
                                        </small>
                                    </div>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 fw-medium">
                                        <i class="bi bi-box-seam me-1"></i> {{ $order->examType->name ?? 'Examen Estándar' }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium text-dark" style="font-size: 0.85rem;">{{ $order->created_at->format('d/m/Y') }}</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">{{ $order->created_at->format('H:i') }} hrs</div>
                            </td>
                            <td>
                                @if($tab === 'available')
                                    @if($isClaimedByOther)
                                        <span class="badge bg-light text-muted border border-secondary-subtle fw-normal">
                                            <i class="bi bi-person-fill-lock me-1"></i> En revisión
                                        </span>
                                    @else
                                        <span class="badge bg-info-subtle text-info border border-info-subtle fw-medium">
                                            Pendiente
                                        </span>
                                    @endif
                                @elseif($tab === 'auto_signed')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-medium">
                                        <i class="bi bi-magic me-1"></i> Autoprocesado
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-medium">
                                        <i class="bi bi-patch-check-fill me-1"></i> Firmado
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($tab === 'available')
                                    @if($isClaimedByOther)
                                        <button class="btn btn-sm btn-light border" disabled>
                                            <i class="bi bi-lock-fill"></i>
                                        </button>
                                    @else
                                        <a href="{{ route('admin.orders.sign.form', $order->id) }}"
                                           class="btn btn-sm {{ $isClaimedByMe ? 'btn-info text-white' : 'btn-primary' }} px-3 rounded-pill shadow-sm fw-bold">
                                            <i class="bi {{ $isClaimedByMe ? 'bi-play-fill' : 'bi-vector-pen' }} me-1"></i>
                                            {{ $isClaimedByMe ? 'Continuar' : 'Firmar' }}
                                        </a>
                                    @endif
                                @else
                                    <div class="btn-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-white border border-end-0" title="Ver Detalles">
                                            <i class="bi bi-eye text-primary"></i>
                                        </a>
                                        {{-- El link de PDF asumiendo que tienes la ruta --}}
                                        <a href="#" class="btn btn-sm btn-white border" title="Descargar PDF">
                                            <i class="bi bi-file-pdf text-danger"></i>
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 bg-light-subtle">
                                <div class="py-4">
                                    <i class="bi bi-inbox fs-1 text-muted d-block mb-3 opacity-50"></i>
                                    <h6 class="text-muted fw-normal">
                                        @if($tab === 'available')
                                            No hay órdenes pendientes por ahora.
                                        @elseif($tab === 'auto_signed')
                                            No hay registros de firmas automáticas.
                                        @else
                                            Aún no has realizado ninguna firma manual.
                                        @endif
                                    </h6>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-top-0 py-3">
        <div class="d-flex justify-content-center">
            {{ $orders->links() }}
        </div>
    </div>
</div>

<style>
    .text-purple { color: #6b21a8 !important; }
    .bg-purple-subtle { background-color: #f3e8ff !important; }
    .border-purple-subtle { border-color: #d8b4fe !important; }
    .btn-white { background-color: #fff; color: #374151; }
    .btn-white:hover { background-color: #f9fafb; }
    .nav-tabs .nav-link.active { color: #0d6efd !important; }
</style>
