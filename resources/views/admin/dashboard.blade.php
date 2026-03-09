@extends('layouts.admin')

@section('header', 'Escritorio de Control')

@section('content')
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Órdenes Hoy</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-calendar-check fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Bienvenido al ecosistema MedOrder</h6>
            </div>
            <div class="card-body">
                <p>Desde aquí podrás gestionar manualmente:</p>
                <ul>
                    <li>Tus <strong>"granjas"</strong> de doctores y sus firmas digitales.</li>
                    <li>El catálogo de <strong>especialidades</strong> médicas.</li>
                    <li>La supervisión técnica del <strong>Químico Farmacéutico</strong>.</li>
                </ul>
                <p class="mb-0">Usa el menú lateral para empezar a cargar datos.</p>
            </div>
        </div>
    </div>
</div>
@endsection
