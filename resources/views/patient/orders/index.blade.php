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
        <div class="empty-state shadow-sm border-0 text-center py-5">
            <div class="mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-soft-bg rounded-circle" style="width: 100px; height: 100px; background-color: #f8f9fa;">
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
                    {{-- Llamada al componente Livewire individual para cada orden --}}
                    @livewire('patient.order-item', ['order' => $order], key('order-item-'.$order->id))
                </div>
            @endforeach
        </div>

        <div class="mt-5 d-flex justify-content-center">
            {{ $orders->links() }}
        </div>
    @endif
</div>

<style>
    /* Estilos globales para la vista de órdenes */
    .fw-800 { font-weight: 800; }
    .bg-info-subtle { background-color: #e0f2fe; color: #0369a1; }
    .bg-success-subtle { background-color: #dcfce7; color: #15803d; }
    .bg-warning-subtle { background-color: #fef3c7; color: #92400e; }
    .card-order { transition: transform 0.2s; }
    .card-order:hover { transform: translateY(-2px); }
</style>
@endsection
