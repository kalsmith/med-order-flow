@extends('layouts.admin')

@section('header', 'Firma de Orden Médica')

@section('header-actions')
    <div class="d-flex gap-2">
        <form action="{{ route('admin.orders.release', ['order' => $order->id]) }}" method="POST">
            @csrf
            <input type="hidden" name="redirect_to" value="index">
            <button type="submit" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Volver al Listado
            </button>
        </form>
    </div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">

        @php
            $prescription = $order->activePrescription;
            $isSigned = $prescription && $prescription->status === 'signed';

            $claimedAt = $order->claimed_at ? \Carbon\Carbon::parse($order->claimed_at) : now();
            $expiresAt = $claimedAt->copy()->addMinutes(20);
            $minutesLeft = max(0, now()->diffInMinutes($expiresAt, false));
            $displayMinutes = ceil($minutesLeft);
        @endphp

        {{-- Alerta de Estado --}}
        @if(!$isSigned)
            <div class="alert bg-white border-start border-4 border-warning shadow-sm py-2 px-3 mb-3 d-flex justify-content-between align-items-center">
                <small class="text-muted fw-bold text-uppercase">
                    <i class="bi bi-hourglass-split text-warning me-1"></i> Sesión de firma activa
                </small>
                <span class="badge bg-warning text-dark fw-bold">
                    Reserva expira en {{ $displayMinutes }} min
                </span>
            </div>
        @else
            <div class="alert bg-success-subtle border-start border-4 border-success shadow-sm py-2 px-3 mb-3 d-flex justify-content-between align-items-center">
                <small class="text-success fw-bold text-uppercase">
                    <i class="bi bi-patch-check-fill me-1"></i> Documento Firmado y Cerrado
                </small>
                <span class="badge bg-success text-white">
                    Emitido el {{ \Carbon\Carbon::parse($prescription->signed_at)->format('d/m/Y H:i') }}
                </span>
            </div>
        @endif

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-file-earmark-medical text-primary me-2"></i>
                        Revisión de Requerimiento #{{ substr($order->id, 0, 8) }}
                    </h5>
                    <span class="badge border {{ $isSigned ? 'bg-success-subtle text-success border-success-subtle' : 'bg-info-subtle text-info border-info-subtle' }} px-3 py-2">
                        {{ $isSigned ? 'Firmado' : 'Pendiente de Firma' }}
                    </span>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-4">
                    {{-- Datos del Paciente --}}
                    <div class="col-md-6 border-end">
                        <label class="text-muted small text-uppercase fw-bold">Paciente</label>
                        <div class="mt-1">
                            <h6 class="mb-0 fw-bold">{{ $order->patient->full_name }}</h6>
                            <p class="text-muted mb-0">RUT: {{ $order->patient->rut }}</p>
                            <p class="text-muted small">Edad: {{ $order->patient->age ?? 'N/A' }} años</p>
                        </div>
                    </div>

                    {{-- Datos de la Orden --}}
                    <div class="col-md-6">
                        <label class="text-muted small text-uppercase fw-bold">Fecha de Solicitud</label>
                        <div class="mt-1">
                            <p class="mb-0">{{ $order->created_at->format('d/m/Y H:i') }} hrs</p>
                            @if($isSigned && $prescription->signed_at)
                                <p class="text-success small mb-0">
                                    <i class="bi bi-clock-history"></i> Firmado: {{ \Carbon\Carbon::parse($prescription->signed_at)->format('d/m/Y H:i') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="col-12">
                        <hr class="opacity-10 my-0">
                    </div>

                    {{-- 1. Detalle del Requerimiento --}}
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

                    {{-- 2. Indicación Médica --}}
                    <div class="col-12">
                        <div class="form-group">
                            <label class="{{ $isSigned ? 'text-muted' : 'text-primary' }} fw-bold mb-2 small text-uppercase">
                                <i class="bi bi-pencil-square me-1"></i> Indicación Médica Profesional (PDF)
                            </label>
                            <textarea name="clinical_context"
                                      form="signature-form"
                                      class="form-control {{ $isSigned ? 'bg-light border-secondary opacity-75' : 'border-primary shadow-sm' }}"
                                      rows="4"
                                      placeholder="Redacte aquí el diagnóstico y los exámenes solicitados..."
                                      {{ $isSigned ? 'readonly' : 'required' }}>{{ old('clinical_context', $isSigned ? $prescription->clinical_context : $order->clinical_context) }}</textarea>
                            @if(!$isSigned)
                                <div class="form-text small text-muted">
                                    <i class="bi bi-info-circle me-1"></i> Esta indicación aparecerá en el PDF final firmado.
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 3. Interacciones (CHAT BLOQUEADO SI ESTÁ FIRMADO) --}}
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-2">
                            <hr class="flex-grow-1 opacity-10">
                            <span class="mx-3 text-muted small fw-bold text-uppercase">
                                <i class="bi bi-chat-dots me-1"></i> Interacción con Paciente
                            </span>
                            <hr class="flex-grow-1 opacity-10">
                        </div>

                        {{-- Pasamos readOnly al componente de Livewire --}}
                        @livewire('admin.order-interactions', ['order' => $order, 'readOnly' => $isSigned])

                        @if($isSigned)
                            <div class="text-center mt-2">
                                <span class="badge bg-light text-muted border border-secondary-subtle">
                                    <i class="bi bi-lock-fill me-1"></i> Chat cerrado por documento firmado
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white p-4 border-top">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center text-md-start mb-3 mb-md-0">
                        <div class="d-inline-block border p-2 bg-light rounded text-center {{ $isSigned ? 'opacity-50' : '' }}" style="min-width: 180px;">
                            <label class="d-block small text-muted mb-1">Sello Estampado</label>
                            @php $sigPath = auth()->user()->doctor->signature_path; @endphp
                            <img src="{{ $sigPath ? asset('storage/' . $sigPath) : asset('images/no-signature.png') }}"
                                 alt="Firma" style="max-height: 50px;" class="mb-1">
                            <div class="small fw-bold border-top pt-1">Dr. {{ auth()->user()->name }}</div>
                        </div>
                    </div>

                    <div class="col-md-9 text-md-end text-center">
                        @if($isSigned)
                            <button type="button" class="btn btn-outline-dark px-4 shadow-sm" disabled>
                                <i class="bi bi-trash3 me-1"></i> Anular Firma
                            </button>

                            <a href="{{ route('admin.orders.pdf', ['order' => $order->id]) }}" target="_blank" class="btn btn-danger btn-lg px-4 shadow ms-2">
                                <i class="bi bi-file-pdf me-2"></i> Ver PDF Firmado
                            </a>
                        @else
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

{{-- Modales --}}
@if(!$isSigned)
    {{-- (Los modales se mantienen iguales...) --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">Rechazar Requerimiento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.orders.reject', ['order' => $order->id]) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="Motivo del rechazo..."></textarea>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger px-4">Confirmar Rechazo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(!$order->exam_type_id)
    <div class="modal fade" id="derivateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Derivar Solicitud</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.orders.derivate', ['order' => $order->id]) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Asignar a Área:</label>
                            <select name="specialty_id" class="form-select" required>
                                <option value="">-- Seleccionar área --</option>
                                @foreach(\App\Models\Specialty::all() as $spec)
                                    <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Confirmar Derivación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endif

<style>
    .bg-info-subtle { background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd !important; }
    .bg-success-subtle { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0 !important; }
    .border-dashed { border-style: dashed !important; }
</style>
@endsection
