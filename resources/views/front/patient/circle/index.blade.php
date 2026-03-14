@extends('layouts.front')

@section('title', 'Mi Círculo - ' . config('app.name'))

@section('content')
<div class="container py-5">
    {{-- Header de la Sección --}}
    <div class="row mb-5 align-items-end">
        <div class="col-md-8">
            <h1 class="fw-800 mb-1" style="letter-spacing: -2px; font-size: 2.5rem;">Mi Círculo Familiar</h1>
            <p class="text-muted mb-0 fs-5">Gestiona los perfiles de tus beneficiarios para órdenes rápidas.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">

            {{-- Componente Principal de Livewire --}}
            @livewire('patient.circle-manager')

            {{-- Caja de Información / Disclaimer --}}
            <div class="mt-5 p-4 rounded-4 bg-white border shadow-sm info-box bg-waiting">
                <div class="d-flex align-items-start text-primary">
                    <i class="bi bi-shield-check fs-4 me-3"></i>
                    <div>
                        <p class="small mb-1 text-dark fw-bold">Verificación de Identidad</p>
                        <p class="small mb-0 text-muted">
                            Los datos de tu círculo se utilizan legalmente para generar órdenes médicas.
                            Asegúrate de que el <strong>RUT y nombre completo</strong> coincidan exactamente con la cédula de identidad vigente del paciente para evitar rechazos en centros médicos.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
