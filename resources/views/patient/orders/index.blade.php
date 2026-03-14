@extends('layouts.front')

@section('title', 'Mis Órdenes - ' . config('app.name'))

@section('content')
    <div class="container py-5">
        @livewire('patient.order-item')
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
