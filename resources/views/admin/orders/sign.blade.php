@extends('layouts.admin')

@section('header', 'Revisión y Firma de Orden')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-lg border-0 mb-4" style="border-radius: 0;">
                <div class="card-body p-5">

                    <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                        <div>
                            <h2 class="text-primary fw-bold mb-0">
                                <i class="bi bi-droplet-fill"></i> MedOrder Flow
                            </h2>
                            <p class="text-muted small mb-0">Centro de Gestión Médica Digital</p>
                        </div>
                        <div class="text-end">
                            <h5 class="fw-bold mb-0">ORDEN MÉDICA</h5>
                            <span class="text-muted small">ID: #{{ $order->id }}</span><br>
                            <span class="badge bg-warning text-dark mt-2">PENDIENTE DE FIRMA</span>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-6">
                            <h6 class="text-uppercase text-muted fw-bold small">Paciente</h6>
                            <p class="mb-0 fw-bold text-dark">{{ $order->patient->user->name }}</p>
                            <p class="mb-0 text-muted">RUT: {{ $order->patient->rut }}</p>
                            <p class="text-muted small small">Edad: {{ \Carbon\Carbon::parse($order->patient->birth_date)->age ?? '--' }} años</p>
                        </div>
                        <div class="col-6 text-end">
                            <h6 class="text-uppercase text-muted fw-bold small">Fecha de Emisión</h6>
                            <p class="text-dark">{{ $order->created_at->format('d \d\e F, Y') }}</p>
                        </div>
                    </div>

                    <div class="bg-light p-4 rounded-3 mb-4 border">
                        <h6 class="text-uppercase text-muted fw-bold small mb-3">Prestación Solicitada</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1 fw-bold text-primary">{{ $order->examType->name }}</h5>
                                <p class="mb-0 text-muted small">{{ $order->examType->description ?? 'Sin descripción adicional.' }}</p>
                            </div>
                            <div class="text-end">
                                <span class="h5 fw-bold text-dark">${{ number_format($order->amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-5 text-center">
                        <div class="border-top mx-auto" style="width: 250px;"></div>
                        <p class="mb-0 fw-bold mt-2 text-dark">Dr. {{ auth()->user()->name }}</p>
<p class="text-muted small">
    Especialidad: {{ auth()->user()->doctor->specialty->name ?? 'No especificada' }}
</p>                        <p class="text-muted small" style="font-size: 0.7rem;">La firma digital se estampará al procesar esta orden.</p>
                    </div>

                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.doctor.panel') }}" class="btn btn-outline-secondary px-4">
                    <i class="bi bi-arrow-left me-2"></i> Volver al listado
                </a>

                <form action="{{ route('admin.orders.sign.process', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success btn-lg px-5 shadow">
                        <i class="bi bi-vector-pen me-2"></i> Confirmar y Firmar Orden
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<style>
    /* Estilo para simular papel en el visor */
    .card {
        background-color: #fff;
        min-height: 800px;
    }
    .text-primary { color: #3b82f6 !important; }
</style>
@endsection
