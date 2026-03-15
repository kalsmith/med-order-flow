@if($isSigned)
    <div class="alert bg-success-subtle border-start border-4 border-success shadow-sm py-2 px-3 mb-3 d-flex justify-content-between align-items-center">
        <small class="text-success fw-bold text-uppercase">
            <i class="bi bi-patch-check-fill me-1"></i> Documento Firmado y Cerrado
        </small>
        <span class="badge bg-success text-white">
            Emitido el {{ \Carbon\Carbon::parse($prescription->signed_at)->format('d/m/Y H:i') }}
        </span>
    </div>
@elseif($isRefundPending || $isRefunded)
    <div class="alert bg-warning-subtle border-start border-4 border-warning shadow-sm py-2 px-3 mb-3 d-flex justify-content-between align-items-center">
        <small class="text-warning-emphasis fw-bold text-uppercase">
            <i class="bi bi-arrow-left-right me-1"></i> {{ $isRefundPending ? 'Reembolso en Proceso' : 'Orden Reembolsada' }}
        </small>
        <span class="badge bg-warning text-dark">
            {{ $isRefundPending ? 'Pendiente Flow' : 'Dinero devuelto' }}
        </span>
    </div>
@elseif($isRejected)
    <div class="alert bg-danger-subtle border-start border-4 border-danger shadow-sm py-2 px-3 mb-3 d-flex justify-content-between align-items-center">
        <small class="text-danger fw-bold text-uppercase">
            <i class="bi bi-x-circle-fill me-1"></i> Requerimiento Rechazado
        </small>
        <span class="badge bg-danger text-white">
            Rechazado el {{ $order->updated_at->format('d/m/Y H:i') }}
        </span>
    </div>
@else
    <div class="alert bg-white border-start border-4 border-warning shadow-sm py-2 px-3 mb-3 d-flex justify-content-between align-items-center">
        <small class="text-muted fw-bold text-uppercase">
            <i class="bi bi-hourglass-split text-warning me-1"></i> Sesión de firma activa
        </small>
        <span class="badge bg-warning text-dark fw-bold">
            Reserva expira en {{ $displayMinutes }} min
        </span>
    </div>
@endif
