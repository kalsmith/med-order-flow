@extends('layouts.admin')

@section('header', 'Firma de Orden Médica')

@section('header-actions')
    {{-- Formulario para liberar la orden explícitamente al salir --}}
    <form action="{{ route('admin.orders.release', $order->id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="bi bi-unlock me-1"></i> Liberar y Volver
        </button>
    </form>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">

        {{-- Alerta de tiempo restante de la "Toma" --}}
        @php
            $expiresAt = $order->claimed_at ? $order->claimed_at->addMinutes(20) : now()->addMinutes(20);
            $minutesLeft = max(0, now()->diffInMinutes($expiresAt, false));
        @endphp

        <div class="alert bg-white border-start border-4 border-warning shadow-sm py-2 px-3 mb-3 d-flex justify-content-between align-items-center">
            <small class="text-muted fw-bold text-uppercase">
                <i class="bi bi-hourglass-split text-warning me-1"></i> Sesión de firma activa
            </small>
            <span class="badge bg-warning text-dark fw-bold">
                Reserva expira en {{ $minutesLeft }} min
            </span>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-file-earmark-medical text-primary me-2"></i>
                        Revisión de Requerimiento #{{ substr($order->id, 0, 8) }}
                    </h5>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3">
                        {{ ucfirst($order->status) }}
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

                    {{-- Detalle del Requerimiento --}}
                    <div class="col-12">
                        <label class="text-muted small text-uppercase fw-bold">Detalle del Examen / Requerimiento</label>
                        <div class="mt-2 p-3 bg-light rounded border">
                            @if($order->examType)
                                <h6 class="fw-bold text-dark mb-1">{{ $order->examType->name }}</h6>
                                <p class="mb-0 text-muted small">{{ $order->examType->description }}</p>
                            @else
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-stars text-purple me-2 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">Solicitud Especial (Custom)</h6>
                                        <p class="mb-0 text-dark" style="white-space: pre-line;">{{ $order->custom_description }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Info sobre la firma --}}
                    <div class="col-12 mt-4">
                        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-0">
                            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                            <div class="small">
                                Al confirmar, se estampará tu <strong>firma digital y registro RNPI</strong> en el documento.
                                Esta acción asignará automáticamente el pago a tu cuenta profesional.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white p-4 border-top">
                <form action="{{ route('admin.orders.process.signature', $order->id) }}" method="POST" id="sign-form">
                    @csrf
                    <div class="row align-items-center">
                        {{-- Visualización de Firma --}}
                        <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                            <div class="d-inline-block border p-2 bg-light rounded text-center" style="min-width: 200px;">
                                <label class="d-block small text-muted mb-1">Sello a Estampar</label>
                                <img src="{{ auth()->user()->doctor->signature_path ? asset('storage/' . auth()->user()->doctor->signature_path) : asset('images/no-signature.png') }}"
                                     alt="Firma" style="max-height: 60px;" class="mb-1">
                                <div class="small fw-bold border-top pt-1">Dr. {{ auth()->user()->name }}</div>
                            </div>
                        </div>

                        {{-- Botones de Acción --}}
                        <div class="col-md-6 text-md-end text-center">
                            <button type="button"
                                    onclick="event.preventDefault(); document.getElementById('release-form').submit();"
                                    class="btn btn-link text-muted text-decoration-none me-3">
                                No estoy seguro
                            </button>
                            <button type="submit" class="btn btn-success btn-lg px-4 shadow">
                                <i class="bi bi-vector-pen me-2"></i> Confirmar y Firmar
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Formulario oculto para liberar la orden sin firmar --}}
                <form id="release-form" action="{{ route('admin.orders.release', $order->id) }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>

        {{-- Advertencia legal --}}
        <div class="text-center px-4">
            <p class="text-muted" style="font-size: 0.7rem; line-height: 1.2;">
                Este sistema cumple con los estándares de la Ley N° 20.584 y el Reglamento sobre Ficha Clínica Electrónica.
                La firma digital tiene validez legal y el proceso queda registrado con trazabilidad completa (IP {{ request()->ip() }}).
            </p>
        </div>
    </div>
</div>

<style>
    .text-purple { color: #7e22ce; }
    .bg-primary-subtle { background-color: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe !important; }
    .border-warning { border-left-color: #ffc107 !important; }
    .btn-link:hover { color: #dc3545 !important; }
</style>
@endsection
