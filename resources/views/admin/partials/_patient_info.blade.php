<div class="col-md-6 border-end">
    <label class="text-muted small text-uppercase fw-bold d-block mb-2">
        <i class="bi bi-person-circle me-1"></i> Información del Paciente
    </label>
    <div class="ps-1">
        <h6 class="mb-1 fw-bold text-dark">{{ $order->patient->full_name }}</h6>
        <p class="mb-1 text-secondary small">
            <span class="fw-medium text-muted">RUT:</span> {{ $order->patient->rut }}
        </p>
        <p class="mb-0 text-secondary small">
            <span class="fw-medium text-muted">Edad:</span> {{ $order->patient->age ?? 'No especificada' }} años
        </p>
    </div>
</div>

<div class="col-md-6">
    <label class="text-muted small text-uppercase fw-bold d-block mb-2">
        <i class="bi bi-calendar-check me-1"></i> Detalle de Solicitud
    </label>
    <div class="ps-1">
        <p class="mb-1 text-secondary small">
            <span class="fw-medium text-muted">Fecha Solicitud:</span>
            {{ $order->created_at->format('d/m/Y H:i') }} hrs
        </p>
        @if($isSigned && $prescription && $prescription->signed_at)
            <p class="mb-0 text-success small fw-bold">
                <i class="bi bi-patch-check-fill"></i>
                Firmado el: {{ \Carbon\Carbon::parse($prescription->signed_at)->format('d/m/Y H:i') }}
            </p>
        @else
            <p class="mb-0 text-info small">
                <i class="bi bi-clock-history"></i> Estado: Procesando revisión
            </p>
        @endif
    </div>
</div>
