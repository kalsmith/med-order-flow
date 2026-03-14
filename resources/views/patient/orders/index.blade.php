@extends('layouts.front')

@section('content')
<div class="container py-5">
    <h2 class="fw-800 mb-4">Mis Órdenes ({{ $orders->count() }})</h2>

    @forelse($orders as $order)
        <div class="card mb-3 p-3 shadow-sm">
             <div class="d-flex justify-content-between align-items-center">
                 <div>
                     <strong>ID:</strong> {{ substr($order->id, 0, 8) }}... <br>
                     <strong>Monto:</strong> ${{ number_format($order->amount, 0, ',', '.') }} <br>
                     <strong>Estado:</strong> {{ $order->status }}
                 </div>
                 <div>
                     {{-- Primero probamos con HTML puro, luego activamos Livewire --}}
                     <span class="badge bg-primary">{{ $order->type }}</span>
                 </div>
             </div>

             {{-- Una vez que veas lo de arriba, descomenta esta línea: --}}
             {{-- @livewire('patient.order-item', ['order' => $order], key($order->id)) --}}
        </div>
    @empty
        <p>No se encontraron órdenes para este perfil de paciente.</p>
    @endforelse
</div>
@endsection
