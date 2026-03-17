@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Órdenes</a></li>
                    <li class="breadcrumb-item active">Auditoría Clínica</li>
                </ol>
            </nav>
            <h2 class="h3 mb-0">Auditoría: #{{ substr($order->id, 0, 8) }}</h2>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            {{-- SECCIÓN 1: Intención Original del Paciente --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                    <span>Solicitud del Paciente</span>
                    <span class="badge bg-{{ $order->status == 'paid' ? 'primary' : 'secondary' }}">
                        {{ strtoupper($order->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="text-muted small d-block fw-bold text-uppercase">Tipo de Servicio</label>
                        <h5 class="fw-bold">
                            @if($order->type === 'custom')
                                <i class="bi bi-pencil-square text-primary"></i> ORDEN PERSONALIZADA (Texto Libre)
                            @else
                                <i class="bi bi-card-list text-success"></i> {{ $order->examType->name ?? 'Examen Estándar' }}
                            @endif
                        </h5>
                    </div>

                    @if($order->type === 'custom')
                        <div class="p-3 bg-light rounded border border-primary mb-3">
                            <label class="text-primary small d-block mb-1 fw-bold">EXÁMENES SOLICITADOS POR EL USUARIO:</label>
                            <p class="mb-0 fw-bold" style="white-space: pre-line;">{{ $order->custom_description }}</p>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-12">
                            <label class="text-muted small d-block fw-bold mb-1">ANAMNESIS / CONTEXTO CLÍNICO:</label>
                            <div class="p-3 bg-light rounded border-start border-4 border-info">
                                {{ $order->clinical_context ?: 'No se proporcionó información clínica adicional por parte del paciente.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

{{-- SECCIÓN 2: Resolución Médica (Prescriptions) --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-file-earmark-medical me-2"></i>Resolución y Documentos Médicos</span>
                    <small class="text-white-50">Auditoría de Prescripciones</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Folio / Fecha</th>
                                    <th>Médico Responsable</th>
                                    <th>Detalle Clínico / Validación</th>
                                    <th>Estado</th>
                                    <th class="text-end pe-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->prescriptions as $p)
                                <tr>
                                    <td class="align-middle ps-3">
                                        <span class="fw-bold text-dark">#{{ $p->correlative_number }}</span><br>
                                        <small class="text-muted"><i class="bi bi-calendar3 me-1"></i>{{ $p->created_at->format('d/m/y H:i') }}</small>
                                    </td>
                                    <td class="align-middle">
                                        @if($p->doctor)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-2 bg-primary-soft text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; background: #e7f1ff;">
                                                    <i class="bi bi-person-badge"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-bold d-block">{{ $p->doctor->prefix }} {{ $p->doctor->user->name }}</span>
                                                    <small class="text-muted text-uppercase" style="font-size: 0.75rem;">RNPI: {{ $p->doctor->rnpi_number }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="badge rounded-pill bg-light text-warning border border-warning">
                                                <i class="bi bi-clock-history me-1"></i> Pendiente de Médico
                                            </span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        {{-- Badge de Tipo de Orden --}}
                                        <div class="mb-2">
                                            @if($p->type === 'custom')
                                                <span class="badge bg-info-soft text-info border border-info small" style="background: #e1f5fe;">
                                                    <i class="bi bi-pencil-square me-1"></i> PERSONALIZADA
                                                </span>
                                            @else
                                                <span class="badge bg-success-soft text-success border border-success small" style="background: #e8f5e9;">
                                                    <i class="bi bi-box-seam me-1"></i> PACK ESTÁNDAR
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Contenido de la validación --}}
                                        <div class="small p-2 rounded {{ $p->type === 'custom' ? 'bg-light border-start border-3 border-info' : 'text-muted' }}">
                                            @if($p->type === 'custom')
                                                <strong>Indicación Médica Final:</strong><br>
                                                {{ $p->clinical_context ?: 'El médico validó la solicitud sin cambios adicionales.' }}
                                            @else
                                                <i class="bi bi-check-circle-fill text-success me-1"></i> Validado: {{ $p->examType->name ?? 'Examen Estándar' }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        @php
                                            $statusClasses = [
                                                'signed' => 'bg-success',
                                                'active' => 'bg-warning text-dark',
                                                'voided' => 'bg-danger',
                                                'expired' => 'bg-secondary'
                                            ];
                                            $currentClass = $statusClasses[$p->status] ?? 'bg-light text-dark';
                                        @endphp
                                        <span class="badge {{ $currentClass }} text-uppercase" style="font-size: 0.7rem;">
                                            {{ $p->status }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-end pe-3">
                                        @if($p->status == 'signed')
                                            <div class="btn-group shadow-sm">
                                                <a href="{{ route('admin.orders.pdf', $order) }}"
                                                   target="_blank"
                                                   class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                    <span>Ver PDF</span>
                                                </a>
                                            </div>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary disabled" title="Documento no disponible">
                                                <i class="bi bi-lock"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-5 text-center">
                                        <img src="https://illustrations.popsy.co/gray/not-found.svg" alt="No data" style="width: 80px;" class="mb-3 opacity-50">
                                        <p class="text-muted">No se han generado prescripciones para esta orden aún.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white py-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        El PDF de la orden sólo está disponible una vez que el médico ha completado la firma digital.
                    </small>
                </div>
            </div>



        </div>

        {{-- Bloque Derecho: Info Paciente y Trazabilidad --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">Paciente</div>
                <div class="card-body">
                    <h6 class="fw-bold mb-1">{{ $order->patient->full_name ?? 'N/A' }}</h6>
                    <p class="text-muted small mb-3">RUT: {{ $order->patient->rut ?? 'N/A' }}</p>
                    <hr>
                    <div class="small">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Previsión:</span> <span class="fw-bold">{{ $order->patient->prevision ?? 'N/A' }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Monto Pagado:</span> <span class="fw-bold text-success">${{ number_format($order->amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold small">Historial de Auditoría</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        @foreach($order->activities ?? [] as $activity)
                        <li class="list-group-item border-0">
                            <i class="bi bi-dot text-primary"></i> <strong>{{ $activity->description }}</strong><br>
                            <span class="text-muted ms-3" style="font-size: 0.85em;">{{ $activity->created_at->format('d/m/y H:i') }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
