<div class="row align-items-center">
    <div class="col-md-3">
        <div class="d-inline-block border p-2 bg-light rounded text-center {{ $isClosed && !$isSigned ? 'opacity-50' : '' }}" style="min-width: 180px;">
            <label class="d-block small text-muted mb-1">Sello</label>
            @php $sigPath = auth()->user()->doctor->signature_path; @endphp
            <img src="{{ $sigPath ? asset('storage/' . $sigPath) : asset('images/no-signature.png') }}" style="max-height: 50px;">
            <div class="small fw-bold border-top mt-1">Dr. {{ auth()->user()->name }}</div>
        </div>
    </div>

    <div class="col-md-9 text-md-end text-center mt-3 mt-md-0">
        @if($isSigned)
            {{-- YA FIRMADO --}}
            <button type="button" class="btn btn-outline-dark px-4" data-bs-toggle="modal" data-bs-target="#voidModal">
                <i class="bi bi-trash3 me-1"></i> Anular Firma
            </button>
            <a href="{{ route('admin.orders.pdf', ['order' => $order->id]) }}" target="_blank" class="btn btn-danger btn-lg px-4 ms-2 shadow">
                <i class="bi bi-file-pdf me-2"></i> Ver PDF
            </a>

        @elseif($isRejected)
            <span class="text-danger fw-bold"><i class="bi bi-x-octagon-fill me-1"></i> REQUERIMIENTO RECHAZADO</span>

        @elseif($isRefundPending || $isRefunded)
            <span class="text-warning fw-bold"><i class="bi bi-cash-stack me-1"></i> PROCESO DE REEMBOLSO</span>

        @else
            {{-- PENDIENTE (Tus registros del INSERT entrarán aquí) --}}
            @if(!$order->exam_type_id)
                <button type="button" class="btn btn-link text-muted me-3" data-bs-toggle="modal" data-bs-target="#derivateModal">
                    <i class="bi bi-person-gear me-1"></i> Derivar
                </button>
            @endif

            <button type="button" class="btn btn-outline-danger px-3 me-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                <i class="bi bi-x-circle me-1"></i> Rechazar
            </button>

            <form action="{{ route('admin.orders.sign.process', ['order' => $order->id]) }}" method="POST" id="signature-form" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success btn-lg px-4 shadow">
                    <i class="bi bi-vector-pen me-2"></i> Confirmar y Firmar
                </button>
            </form>
        @endif
    </div>
</div>
