@extends('layouts.front')

@section('title', 'Mis Órdenes - ' . config('app.name'))

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <h2 class="fw-800 text-dark mb-4" style="letter-spacing: -1.5px;">Mis Órdenes</h2>

            @forelse($orders as $order)
                {{-- Pasamos cada orden al componente hijo --}}
                {{-- @livewire('patient.order-item', ['order' => $order], key('order-'.$order->id)) --}}
            @empty
                <div class="card border-0 shadow-sm rounded-4 py-5 text-center">
                    <div class="card-body">
                        <i class="bi bi-clipboard2-x fs-1 text-muted opacity-50"></i>
                        <p class="text-muted mt-3">Aún no tienes órdenes de exámenes registradas.</p>
                        <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4">
                            Solicitar Orden Médica
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .fw-800 { font-weight: 800; }
    .card-order { border-radius: 16px; transition: all 0.3s ease; }
    /* Estilos base para que la vista cargue limpia */
</style>
@endpush
