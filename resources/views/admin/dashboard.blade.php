@extends('layouts.admin')

@section('header', 'Escritorio de Control')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        {{-- Widget: Órdenes Hoy --}}
        <div class="col-md-6 col-xl-4 mb-4">
            <div class="card border-0 border-start border-primary border-4 shadow-sm h-100 py-2 rounded-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2 ps-3">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Órdenes Generadas (Hoy)</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $todayOrders }}</div>
                        </div>
                        <div class="col-auto pe-3">
                            <i class="bi bi-calendar-check fs-1 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Widget: Pendientes de Firma --}}
        <div class="col-md-6 col-xl-4 mb-4">
            <div class="card border-0 border-start border-warning border-4 shadow-sm h-100 py-2 rounded-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2 ps-3">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Esperando Firma Médica</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $pendingSignature }}</div>
                        </div>
                        <div class="col-auto pe-3">
                            <i class="bi bi-pen-fill fs-1 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bienvenida y Accesos Rápidos --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm mb-4 rounded-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="m-0 font-weight-bold text-primary">Bienvenido al ecosistema MedOrder</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Gestión centralizada de operaciones clínicas y financieras:</p>
                    <div class="row mt-4">
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary-subtle p-3 rounded-circle me-3">
                                    <i class="bi bi-people text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Médicos</h6>
                                    <small class="text-muted">Control de firmas digitales.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-success-subtle p-3 rounded-circle me-3">
                                    <i class="bi bi-flask text-success"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Catálogo</h6>
                                    <small class="text-muted">Especialidades y exámenes.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-info-subtle p-3 rounded-circle me-3">
                                    <i class="bi bi-shield-check text-info"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Supervisión</h6>
                                    <small class="text-muted">Director Técnico y DT.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
