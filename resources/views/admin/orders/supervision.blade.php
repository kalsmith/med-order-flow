@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-dark fw-bold">
            <i class="bi bi-shield-check me-2 text-primary"></i>Supervisión de Órdenes Médicas
        </h2>
        {{-- Aquí podrías poner un botón de exportar Excel si lo necesitas en el futuro --}}
    </div>

    @livewire('admin.order-supervision')
</div>
@endsection
