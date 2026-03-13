@extends('layouts.front')

@section('title', 'Mis Órdenes - MedOrder Flow')

@section('content')
<div class="container py-5">
    {{-- Header de la Sección --}}
    <div class="row mb-5 align-items-end">
        <div class="col-md-6">
            <h1 class="fw-800 mb-1" style="letter-spacing: -2px; font-size: 2.5rem;">Mis Órdenes Médicas</h1>
            <p class="text-muted mb-0 fs-5">Gestiona tus solicitudes y descarga tus documentos.</p>
        </div>
        <div class="col-md-6 text-md-end mt-4 mt-md-0">
            <a href="{{ route('home') }}" class="btn btn-primary shadow-sm px-4 py-3 fw-bold">
                <i class="bi bi-plus-lg me-2"></i> Nueva Solicitud
            </a>
        </div>
    </div>

    @if($orders->isEmpty())
        {{-- Empty State: Se activa cuando no hay órdenes --}}
        <div class="empty-state shadow-sm border-0">
            <div class="mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-soft-bg rounded-circle" style="width: 100px; height: 100px;">
                    <i class="bi bi-clipboard2-pulse text-primary" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
            <h2 class="fw-800 text-dark" style="letter-spacing: -1px;">No hay órdenes registradas</h2>
            <p class="text-muted mx-auto mb-4" style="max-width: 400px;">
                Parece que aún no has realizado ninguna solicitud. Comienza ahora y obtén tu orden médica en minutos.
            </p>
            <a href="{{ route('home') }}" class="btn btn-primary px-5 py-3 fw-bold">
                Crear mi primera orden
            </a>
        </div>
    @else
        <div class="row g-4">
            @foreach($orders as $order)
            <div class="col-12">
                <div class="card card-order border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">

                            {{-- LADO IZQUIERDO: Información del Examen --}}
                            <div class="col-lg-7">
                                <div class="d-flex align-items-center mb-3 flex-wrap gap-2">
                                    <span class="badge badge-id px-3 py-2">
                                        ID: {{ strtoupper(substr($order->id, 0, 8)) }}
                                    </span>
                                    <span class="text-muted small mx-2">
                                        <i class="bi bi-calendar3 me-1"></i> {{ $order->created_at->format('d/m/Y H:i') }}
                                    </span>
                                    <span class="patient-tag text-uppercase px-3">
                                        <i class="bi bi-person-circle me-1"></i> {{ $order->patient->full_name ?? 'TITULAR' }}
                                    </span>
                                </div>

                                <h2 class="fw-800 text-dark mb-0" style="letter-spacing: -1.2px; font-size: 1.85rem;">
                                    {{ $order->type === 'custom' ? 'Solicitud Especial' : ($order->examType->name ?? 'Examen General') }}
                                </h2>

                                @if($order->status === 'paid')
                                    <div class="mt-4 p-3 info-box bg-waiting d-inline-flex align-items-center">
                                        <div class="spinner-border spinner-border-sm text-primary me-3" role="status"></div>
                                        <div>
                                            <span class="d-block fw-bold text-dark small">Médico revisando su solicitud</span>
                                            <span class="text-muted d-block" style="font-size: 0.75rem;">Plazo: 24 horas aprox.</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- LADO DERECHO: Precio y Botón --}}
                            <div class="col-lg-5 mt-4 mt-lg-0 text-lg-end">
                                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-end gap-4">

                                    {{-- Precio y Estado --}}
                                    <div class="order-lg-1">
                                        @php
                                            $statusConfig = [
                                                'pending' => ['class' => 'badge-status-pending', 'label' => 'PENDIENTE'],
                                                'paid'    => ['class' => 'bg-info-subtle text-info', 'label' => 'EN REVISIÓN'],
                                                'signed'  => ['class' => 'badge-status-signed', 'label' => 'LISTA PARA DESCARGA'],
                                                'rejected'=> ['class' => 'bg-danger-subtle text-danger', 'label' => 'RECHAZADA'],
                                            ];
                                            $curr = $statusConfig[$order->status] ?? ['class' => 'bg-secondary-subtle', 'label' => strtoupper($order->status)];
                                        @endphp

                                        <span class="badge {{ $curr['class'] }} mb-2 fw-bold px-3 py-2" style="font-size: 0.7rem;">
                                            {{ $curr['label'] }}
                                        </span>

                                        <div class="d-block">
                                            <span class="fw-800 text-dark" style="font-size: 2.8rem; letter-spacing: -2px; line-height: 1;">
                                                $ {{ number_format($order->amount, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Botón de Acción --}}
                                    <div class="order-lg-2">
                                        @if($order->status === 'signed')
                                            <a href="{{ route('orders.download', $order->id) }}" class="btn btn-success d-flex align-items-center gap-2">
                                                <i class="bi bi-file-earmark-pdf-fill fs-5"></i> Descargar PDF
                                            </a>
                                        @elseif($order->status === 'pending')
                                            <a href="{{ route('checkout.process', $order->id) }}" class="btn btn-primary px-4">
                                                Pagar Ahora
                                            </a>
                                        @endif
                                    </div>

                                </div>
                            </div> {{-- Fin Columna Derecha --}}

                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-5 d-flex justify-content-center">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
