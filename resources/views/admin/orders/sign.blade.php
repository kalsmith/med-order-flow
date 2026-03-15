@extends('layouts.admin')

@php
    /**
     * Definición de variables para controlar el estado de bloqueo
     */
    $prescription = $order->activePrescription;

    // Estados base
    $isSigned = $prescription && $prescription->status === 'signed';
    $isRejected = $order->status === 'rejected';
    $isRefundPending = $order->status === 'refund_pending';
    $isRefunded = $order->status === 'refunded';

    // Si está firmada, rechazada o en proceso de reembolso, la orden está "Cerrada" para edición
    $isClosed = $isSigned || $isRejected || $isRefundPending || $isRefunded;

    $claimedAt = $order->claimed_at ? \Carbon\Carbon::parse($order->claimed_at) : now();
    $expiresAt = $claimedAt->copy()->addMinutes(20);
    $minutesLeft = max(0, now()->diffInMinutes($expiresAt, false));
    $displayMinutes = ceil($minutesLeft);
@endphp

@section('header', 'Firma de Orden Médica')

@section('header-actions')
    <div class="d-flex gap-2">
        @if(!$isClosed)
            <form action="{{ route('admin.orders.release', ['order' => $order->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="redirect_to" value="index">
                <button type="submit" class="btn btn-outline-danger btn-sm shadow-sm">
                    <i class="bi bi-unlock me-1"></i> Liberar y Volver
                </button>
            </form>
        @else
            <a href="{{ route('admin.doctor.panel') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Volver al Listado
            </a>
        @endif
    </div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">

        {{-- 1. Alertas de Estado --}}
        @include('admin.partials._status_alerts', [
            'order' => $order,
            'prescription' => $prescription,
            'isSigned' => $isSigned,
            'isRejected' => $isRejected,
            'isRefundPending' => $isRefundPending,
            'isRefunded' => $isRefunded,
            'displayMinutes' => $displayMinutes
        ])

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-file-earmark-medical text-primary me-2"></i>
                        Revisión de Requerimiento #{{ substr($order->id, 0, 8) }}
                    </h5>

                    @php
                        $badgeClass = $isSigned ? 'bg-success-subtle text-success border-success-subtle' :
                                     ($isRejected ? 'bg-danger-subtle text-danger border-danger-subtle' :
                                     ($isRefundPending || $isRefunded ? 'bg-warning-subtle text-warning border-warning-subtle' : 'bg-info-subtle text-info border-info-subtle'));

                        $statusText = $isSigned ? 'Firmado' :
                                     ($isRejected ? 'Rechazado' :
                                     ($isRefundPending ? 'Reembolso Pendiente' :
                                     ($isRefunded ? 'Reembolsado' : 'Pendiente de Firma')));
                    @endphp
                    <span class="badge border {{ $badgeClass }} px-3 py-2">
                        {{ $statusText }}
                    </span>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-4">
                    {{-- 2. Datos del Paciente --}}
                    @include('admin.partials._patient_info', [
                        'order' => $order,
                        'prescription' => $prescription,
                        'isSigned' => $isSigned
                    ])

                    {{-- Motivo de Rechazo / Reembolso --}}
                    @if($isRejected || $isRefundPending || $isRefunded)
                        <div class="col-12">
                            <div class="p-3 bg-danger-subtle border border-danger rounded">
                                <label class="text-danger small text-uppercase fw-bold d-block mb-1">Motivo del Rechazo Profesional</label>
                                <p class="mb-0 text-dark fw-medium">{{ $order->rejection_reason }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="col-12">
                        <hr class="opacity-10 my-0">
                    </div>

                    {{-- 3. Detalle del Requerimiento --}}
                    <div class="col-12">
                        <label class="text-muted small text-uppercase fw-bold">Motivo de Consulta (Usuario)</label>
                        <div class="mt-2 p-3 bg-light rounded border border-dashed">
                            @if($order->examType)
                                <h6 class="fw-bold text-dark mb-1">{{ $order->examType->name }}</h6>
                                <p class="mb-0 text-muted small">{{ $order->examType->description }}</p>
                            @else
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-chat-quote text-secondary me-2 mt-1"></i>
                                    <p class="mb-0 text-dark fst-italic">"{{ $order->custom_description }}"</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 4. Indicación Médica Profesional --}}
                    <div class="col-12">
                        <div class="form-group">
                            <label class="{{ $isClosed ? 'text-muted' : 'text-primary' }} fw-bold mb-2 small text-uppercase">
                                <i class="bi bi-pencil-square me-1"></i> Indicación Médica Profesional (PDF)
                            </label>
                            <textarea name="clinical_context"
                                      form="signature-form"
                                      class="form-control {{ $isClosed ? 'bg-light border-secondary opacity-75' : 'border-primary shadow-sm' }}"
                                      rows="4"
                                      placeholder="Redacte aquí el diagnóstico y los exámenes solicitados..."
                                      {{ $isClosed ? 'readonly' : 'required' }}>{{ old('clinical_context', $isSigned ? $prescription->clinical_context : $order->clinical_context) }}</textarea>
                            @if(!$isClosed)
                                <div class="form-text small text-muted">
                                    <i class="bi bi-info-circle me-1"></i> Esta indicación aparecerá en el PDF final firmado.
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 5. Interacciones (Livewire) --}}
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-2">
                            <hr class="flex-grow-1 opacity-10">
                            <span class="mx-3 text-muted small fw-bold text-uppercase">
                                <i class="bi bi-chat-dots me-1"></i> Interacción con Paciente
                            </span>
                            <hr class="flex-grow-1 opacity-10">
                        </div>

                        @livewire('admin.order-interactions', ['order' => $order, 'readOnly' => $isClosed])

                        @if($isClosed)
                            <div class="text-center mt-2">
                                <span class="badge bg-light text-muted border border-secondary-subtle">
                                    <i class="bi bi-lock-fill me-1"></i> Chat cerrado ({{ $isSigned ? 'Documento firmado' : 'Orden cerrada' }})
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white p-4 border-top">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center text-md-start mb-3 mb-md-0">
                        <div class="d-inline-block border p-2 bg-light rounded text-center {{ $isClosed ? 'opacity-50' : '' }}" style="min-width: 180px;">
                            <label class="d-block small text-muted mb-1">Sello Estampado</label>
                            @php $sigPath = auth()->user()->doctor->signature_path; @endphp
                            <img src="{{ $sigPath ? asset('storage/' . $sigPath) : asset('images/no-signature.png') }}"
                                 alt="Firma" style="max-height: 50px;" class="mb-1">
                            <div class="small fw-bold border-top pt-1">Dr. {{ auth()->user()->name }}</div>
                        </div>
                    </div>

                    <div class="col-md-9 text-md-end text-center">
                        @if($isSigned)
                            <button type="button" class="btn btn-outline-dark px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#voidModal">
                                <i class="bi bi-trash3 me-1"></i> Anular Firma
                            </button>

                            <a href="{{ route('admin.orders.pdf', ['order' => $order->id]) }}" target="_blank" class="btn btn-danger btn-lg px-4 shadow ms-2">
                                <i class="bi bi-file-pdf me-2"></i> Ver PDF Firmado
                            </a>
                        @elseif($isRefundPending || $isRefunded)
                             <span class="text-warning fw-bold me-3">
                                <i class="bi bi-cash-stack me-1"></i> ORDEN EN PROCESO DE REEMBOLSO
                             </span>
                        @elseif($isRejected)
                             <span class="text-danger fw-bold me-3">
                                <i class="bi bi-x-octagon-fill me-1"></i> ESTE REQUERIMIENTO FUE RECHAZADO
                             </span>
                        @else
                            {{-- Caso: Pendiente de Firma --}}
                            @if(!$order->exam_type_id)
                                <button type="button" class="btn btn-link text-decoration-none text-muted me-3 shadow-none" data-bs-toggle="modal" data-bs-target="#derivateModal">
                                    <i class="bi bi-person-gear me-1"></i> Derivar
                                </button>
                            @endif

                            <button type="button" class="btn btn-outline-danger px-3 me-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
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
            </div>
        </div>
    </div>
</div>

{{-- 6. Modales de Acción --}}
@include('admin.partials._modals', [
    'order' => $order,
    'isSigned' => $isSigned,
    'isClosed' => $isClosed
])

<style>
    .bg-info-subtle { background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd !important; }
    .bg-success-subtle { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0 !important; }
    .bg-danger-subtle { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca !important; }
    .bg-warning-subtle { background-color: #fef9c3; color: #854d0e; border: 1px solid #fde68a !important; }
    .border-dashed { border-style: dashed !important; }
</style>
@endsection
