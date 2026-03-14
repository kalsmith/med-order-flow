@extends('layouts.front')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <h2 class="fw-800 text-dark mb-4">Mis Órdenes Médicas</h2>

            @forelse($orders as $order)
                {{-- Ahora sí, activamos el componente con su key --}}
                @livewire('patient.order-item', ['order' => $order], key($order->id))
            @empty
                @endforelse
        </div>
    </div>
</div>
@endsection
