@extends('layouts.admin')

@section('header', 'Firma de Orden Médica')

@section('header-actions')
    <div class="d-flex gap-2">
        <form action="{{ route('admin.orders.release', ['medical_order' => $order->id]) }}" method="POST">
            @csrf
            <input type="hidden" name="redirect_to" value="index">
            <button type="submit" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="bi bi-unlock me-1"></i> Liberar y Volver
            </button>
        </form>
    </div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">

        {{-- Alerta de tiempo restante --}}
        @php
            $expiresAt = $order->claimed_at ? $order->claimed_at->addMinutes(20) : now()->addMinutes(20);
            $minutesLeft = max(0, now()->diffInMinutes($expiresAt, false));
            $displayMinutes = ceil($minutesLeft);
        @endphp

        <div class="alert bg-white border-start border-4 border-warning shadow-sm py-2 px-3 mb-3 d-flex justify-content-between align-items-center">
            <small class="text-muted fw-bold text-uppercase">
                <i class="bi bi-hourglass-split text-warning me-1"></i> Sesión de firma activa
            </small>
            <span class="badge bg-warning text-dark fw-bold">
                Reserva expira en {{ $displayMinutes }} min
            </span>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-file-earmark-medical text-primary me-2"></i>
                        Revisión de Requerimiento #{{ substr($order->id, 0, 8) }}
                    </h5>
                    @php
                        $badgeClasses = [
                            'paid' => 'bg-info-subtle text-info-emphasis border-info-subtle',
                            'pending' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle'
                        ];
                    @endphp
                    <span class="badge border {{ $badgeClasses[$order->status] ?? 'bg-light' }} px-3 py-2">
                        {{ $order->status === 'paid' ? 'Pagada / Lista para Firma' : ucfirst($order->status) }}
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
                            <p class="text-muted small">Edad: {{ $order->patient->age ?? 'No especificada' }} años</p>
                        </div>
                    </div>

                    {{-- Datos de la Orden --}}
                    <div class="col-md-6">
                        <label class="text-muted small text-uppercase fw-bold">Fecha de Solicitud</label>
                        <div class="mt-1">
                            <p class="mb-0">{{ $order->created_at->format('d/m/Y H:i') }} hrs</p>
                            <p class="text-primary fw-bold mb-0">Honorarios: ${{ number_format($order->amount, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="col-12">
                        <hr class="opacity-10 my-0">
                    </div>

                    {{-- COMPONENTE LIVEWIRE: Historial y Chat --}}
                    <div class="col-12">
                        @livewire('admin.order-interactions', ['order' => $order])
                    </div>

                    {{-- Formulario de Firma --}}
                    <div class="col-12">
                        <div class="form-group">
                            <label class="text-primary fw-bold mb-2 small text-uppercase">
                                <i class="bi bi-pencil-square me-1"></i> Indicación Médica Profesional
                            </label>
                            <textarea name="clinical_context"
                                      form="signature-form"
                                      class="form-control border-primary shadow-sm"
                                      rows="4"
                                      placeholder="Redacte aquí el diagnóstico y los exámenes solicitados..."
                                      required>{{ old('clinical_context', $order->clinical_context) }}</textarea>
                            <div class="form-text small text-muted">
                                <i class="bi bi-info-circle me-1"></i> Esta indicación aparecerá en el PDF firmado.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white p-4 border-top">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center text-md-start mb-3 mb-md-0">
                        <div class="d-inline-block border p-2 bg-light rounded text-center" style="min-width: 180px;">
                            <label class="d-block small text-muted mb-1">Sello a Estampar</label>
                            @php
                                $sigPath = auth()->user()->doctor->signature_path;
                            @endphp
                            <img src="{{ $sigPath ? asset('storage/' . $sigPath) : asset('images/no-signature.png') }}"
                                 alt="Firma" style="max-height: 50px;" class="mb-1">
                            <div class="small fw-bold border-top pt-1">Dr. {{ auth()->user()->name }}</div>
                        </div>
                    </div>

                    <div class="col-md-9 text-md-end text-center">
                        @if(!$order->exam_type_id)
                            <button type="button" class="btn btn-link text-decoration-none text-muted me-3 shadow-none" data-bs-toggle="modal" data-bs-target="#derivateModal">
                                <i class="bi bi-person-gear me-1"></i> Derivar a Especialidad
                            </button>
                        @endif

                        <button type="button" class="btn btn-outline-danger px-3 me-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle me-1"></i> Rechazar Orden
                        </button>

                        <form action="{{ route('admin.orders.sign.process', ['medical_order' => $order->id]) }}"
                              method="POST" id="signature-form" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg px-4 shadow">
                                <i class="bi bi-vector-pen me-2"></i> Confirmar y Firmar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center px-4">
            <p class="text-muted" style="font-size: 0.7rem; line-height: 1.2;">
                Este sistema cumple con los estándares de la Ley N° 20.584. IP: {{ request()->ip() }}
            </p>
        </div>
    </div>
</div>

@include('admin.orders.partials.modals-sign')

<style>
    .bg-info-subtle { background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd !important; }
    .bg-warning-subtle { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a !important; }
</style>
@endsection
