@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Órdenes</a></li>
                    <li class="breadcrumb-item active">Auditoría</li>
                </ol>
            </nav>
            <h2 class="h3 mb-0">Ficha de Auditoría: #{{ substr($order->id, 0, 8) }}</h2>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Volver al listado
        </a>
    </div>

    <div class="row">
        {{-- Bloque Izquierdo: Información del Paciente y Contenido Clínico --}}
        <div class="col-md-8">
            {{-- Card de Detalles del Examen / Pack --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                    <span>Contenido de la Orden</span>
                    <span class="badge bg-{{ $order->status == 'paid' ? 'primary' : 'success' }}">
                        {{ strtoupper($order->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="text-muted small d-block">Examen Solicitado</label>
                            <h4 class="fw-bold text-dark">
                                {{ $order->display_name }}
                                @if($order->examType?->isProfile())
                                    <span class="badge bg-info text-dark ms-2" style="font-size: 0.5em; vertical-align: middle;">
                                        <i class="bi bi-box-seam"></i> PACK / PERFIL
                                    </span>
                                @endif
                            </h4>
                        </div>
                    </div>

                    {{-- Desglose si es un Pack --}}
                    @if($order->examType?->isProfile())
                        <div class="p-3 border rounded bg-light mb-3">
                            <label class="text-muted small d-block mb-2 fw-bold text-uppercase">
                                <i class="bi bi-list-check"></i> Exámenes incluidos en este pack:
                            </label>
                            <div class="row">
                                @foreach($order->examType->children->chunk(ceil($order->examType->children->count() / 2)) as $chunk)
                                    <div class="col-md-6">
                                        <ul class="list-unstyled mb-0 small">
                                            @foreach($chunk as $child)
                                                <li class="mb-1">
                                                    <i class="bi bi-check2-circle text-success"></i>
                                                    {{ $child->name }}
                                                    <span class="text-muted">({{ $child->code_fonasa ?: 'S/C' }})</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="text-muted small">Código Fonasa:</label>
                            <span class="fw-bold">{{ $order->examType->code_fonasa ?? 'No especificado' }}</span>
                        </div>
                    @endif

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <label class="text-muted small d-block fw-bold text-uppercase mb-2">Contexto Clínico (Anamnesis del Paciente)</label>
                            <div class="p-3 bg-light rounded border-start border-4 border-primary">
                                {{ $order->clinical_context ?: 'El paciente no proporcionó contexto clínico adicional.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Historial de Prescripciones / Firmas --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Documentos Médicos y Firmas</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">Correlativo</th>
                                    <th class="border-0">Médico Responsable</th>
                                    <th class="border-0">Fecha Firma</th>
                                    <th class="border-0">Estado</th>
                                    <th class="border-0 text-end">Documento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->prescriptions as $p)
                                <tr>
                                    <td class="align-middle fw-bold">#{{ $p->correlative_number }}</td>
                                    <td class="align-middle">
                                        {{ $p->doctor->name }}<br>
                                        <small class="text-muted">RNPI: {{ $p->doctor->rnpi_number }}</small>
                                    </td>
                                    <td class="align-middle small">
                                        {{ $p->signed_at ? $p->signed_at->format('d/m/Y H:i') : 'Pendiente de firma' }}
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge {{ $p->status == 'signed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ strtoupper($p->status) }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-end">
                                        @if($p->status == 'signed')
                                        <a href="{{ route('admin.orders.pdf', $order) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-file-earmark-pdf"></i> Ver PDF
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted">No se han generado prescripciones para esta orden.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bloque Derecho: Datos del Paciente y Auditoría --}}
        <div class="col-md-4">
            {{-- Card Paciente --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">Información del Paciente</div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="bi bi-person-fill h4 mb-0"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 fw-bold">{{ $order->patient->full_name ?? 'N/A' }}</h6>
                            <small class="text-muted">RUT: {{ $order->patient->rut ?? 'N/A' }}</small>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Edad:</span> <span class="fw-bold">{{ $order->patient->age ?? 'N/A' }} años</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Previsión:</span> <span class="fw-bold">{{ $order->patient->prevision ?? 'N/A' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Activity Log --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                    <span class="small">Log de Eventos</span>
                    <i class="bi bi-clock-history small"></i>
                </div>
                <div class="card-body p-0">
                    <div style="max-height: 400px; overflow-y: auto;">
                        <ul class="list-group list-group-flush small">
                            @forelse($order->activities ?? [] as $activity)
                            <li class="list-group-item border-0 pb-0">
                                <div class="d-flex">
                                    <div class="text-primary me-2">•</div>
                                    <div>
                                        <p class="mb-0 fw-bold">{{ $activity->description }}</p>
                                        <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li class="list-group-item text-center text-muted">Sin registros de actividad.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
