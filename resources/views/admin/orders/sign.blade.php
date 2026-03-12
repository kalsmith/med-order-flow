@extends('layouts.admin')

@section('header', 'Firma de Orden Médica')

@section('header-actions')
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
        <i class="bi bi-arrow-left me-1"></i> Cancelar y Volver
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-file-earmark-medical text-primary me-2"></i>
                        Revisión de Requerimiento #{{ substr($order->id, 0, 8) }}
                    </h5>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3">
                        {{ ucfirst($order->type) }}
                    </span>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-4">
                    {{-- Datos del Paciente --}}
                    <div class="col-md-6">
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
                            <p class="text-primary fw-bold mb-0">Monto: ${{ number_format($order->amount, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="col-12">
                        <hr class="opacity-10">
                    </div>

                    {{-- Detalle del Requerimiento --}}
                    <div class="col-12">
                        <label class="text-muted small text-uppercase fw-bold">Detalle del Examen / Requerimiento</label>
                        <div class="mt-2 p-3 bg-light rounded border">
                            @if($order->examType)
                                <h6 class="fw-bold text-dark mb-1">{{ $order->examType->name }}</h6>
                                <p class="mb-0 text-muted">{{ $order->examType->description }}</p>
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

                    {{-- Firma del Médico --}}
                    <div class="col-12 mt-4">
                        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center">
                            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                            <div>
                                Al hacer clic en <strong>"Confirmar y Firmar"</strong>, se estampará tu firma digital y registro RNPI en el documento final que recibirá el paciente.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white p-4 border-top">
                <form action="{{ route('admin.orders.process.signature', $order->id) }}" method="POST">
                    @csrf
                    <div class="row align-items-center">
                        <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                            <div class="d-inline-block border p-2 bg-light rounded text-center">
                                <label class="d-block small text-muted mb-1">Tu Sello Digital</label>
                                <img src="{{ auth()->user()->doctor->signature_path ? asset('storage/' . auth()->user()->doctor->signature_path) : asset('images/no-signature.png') }}"
                                     alt="Firma" style="max-height: 60px;">
                                <div class="small fw-bold mt-1">Dr. {{ auth()->user()->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end text-center">
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-link text-muted text-decoration-none me-3">
                                No estoy seguro
                            </a>
                            <button type="submit" class="btn btn-success btn-lg px-4 shadow">
                                <i class="bi bi-vector-pen me-2"></i> Confirmar y Firmar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Advertencia legal pequeña --}}
        <div class="text-center">
            <p class="text-muted" style="font-size: 0.7rem;">
                Este sistema cumple con los estándares de la Ley de Derechos y Deberes del Paciente.
                Cada firma queda registrada con trazabilidad de IP y marca de tiempo.
            </p>
        </div>
    </div>
</div>

<style>
    .text-purple { color: #7e22ce; }
    .bg-primary-subtle { background-color: #e0e7ff; }
</style>
@endsection
