@extends('layouts.front')

@section('title', 'Mis Órdenes - ' . config('app.name'))

@section('content')
    <div class="container py-5">
        <h2 class="mb-4 fw-bold">Mis Órdenes Médicas</h2>

        @forelse($orders as $order)
            @livewire('patient.order-item', ['order' => $order], key($order->id))
        @empty
            <div class="text-center py-5">
                <i class="bi bi-clipboard-x fs-1 text-muted"></i>
                <p class="mt-3">Aún no tienes órdenes registradas.</p>
            </div>
        @endforelse
    </div>
@endsection

@push('styles')
<style>
    .fw-800 { font-weight: 800; }
    .bg-info-subtle { background-color: #e0f2fe; color: #0369a1; }
    .bg-success-subtle { background-color: #dcfce7; color: #15803d; }
    .bg-warning-subtle { background-color: #fef3c7; color: #92400e; }
    .card-order { transition: transform 0.2s; border-radius: 16px; }
    .card-order:hover { transform: translateY(-2px); }
</style>
@endpush
