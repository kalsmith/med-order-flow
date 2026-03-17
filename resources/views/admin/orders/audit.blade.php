@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item small"><a href="{{ route('admin.orders.index') }}">Órdenes</a></li>
                    <li class="breadcrumb-item active small">Auditoría Clínica</li>
                </ol>
            </nav>
            <h2 class="h3 mb-0 fw-bold">
                Auditoría:
                <span class="text-primary">#{{ $order->activePrescription->correlative_number ?? substr($order->id, 0, 8) }}</span>
            </h2>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Volver al Listado
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            {{-- SECCIÓN 1: Intención Original del Paciente --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-person-badge me-2"></i>Solicitud Original</span>
                    <span class="badge {{ $order->status == 'paid' ? 'bg-primary' : 'bg-secondary' }} rounded-pill">
                        PAGO: {{ strtoupper($order->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="text-muted small d-block fw-bold text-uppercase mb-2">Tipo de Servicio Solicitado</label>
                        <h5 class="fw-bold text-dark">
                            @if($order->type === 'custom')
                                <span class="text-primary"><i class="bi bi-pencil-square"></i> ORDEN PERSONALIZADA</span>
                            @else
                                <span class="text-success"><i class="bi bi-card-list"></i> {{ $order->examType->name ?? 'Examen Estándar' }}</span>
                            @endif
                        </h5>
                    </div>

                    @if($order->type === 'custom')
                        <div class="p-3 bg-light rounded border-start border-4 border-primary mb-4">
                            <label class="text-primary small d-block mb-2 fw-bold text-uppercase">Exámenes descritos por el usuario:</label>
                            <p class="mb-0 fw-bold text-dark" style="white-space: pre-line; line-height: 1.6;">{{ $order->custom_description }}</p>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-12">
                            <label class="text-muted small d-block fw-bold mb-2 text-uppercase">Anamnesis / Contexto Clínico:</label>
                            <div class="p-3 bg-light rounded border-start border-4 border-info italic text-dark">
                                {{ $order->clinical_context ?: 'No se proporcionó información clínica adicional por parte del paciente.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN 2: Resolución Médica (Prescriptions) --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-file-earmark-medical me-2"></i>Resolución y Folios Generados</span>
                    <small class="opacity-75">Trazabilidad Médica</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light small text-uppercase text-muted">
                                <tr>
                                    <th class="ps-3 py-3">Folio / Fecha</th>
                                    <th>Médico Responsable</th>
                                    <th>Validación Clínica</th>
                                    <th>Estado</th>
                                    <th class="text-end pe-3">PDF</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->prescriptions as $p)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark">#{{ $p->correlative_number }}</div>
                                        <small class="text-muted">{{ $p->created_at->format('d/m/y H:i') }}</small>
                                    </td>
                                    <td>
                                        @if($p->doctor)
                                            <div class="fw-bold text-dark">{{ $p->doctor->prefix }} {{ $p->doctor->user->name ?? 'Médico' }}</div>
                                            <small class="text-muted small">RNPI: {{ $p->doctor->rnpi_number }}</small>
                                        @else
                                            <span class="text-warning small italic">No asignado</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small fw-medium">
                                            @if($p->type === 'custom')
                                                <span class="text-info"><i class="bi bi-info-circle me-1"></i>Personalizada</span>
                                            @else
                                                <span class="text-success"><i class="bi bi-check-all me-1"></i>Estándar</span>
                                            @endif
                                        </div>
                                        <div class="text-muted small text-truncate" style="max-width: 200px;">
                                            {{ $p->clinical_context ?? 'Sin observaciones' }}
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = [
                                                'signed' => 'bg-success',
                                                'active' => 'bg-warning text-dark',
                                                'voided' => 'bg-danger',
                                            ][$p->status] ?? 'bg-secondary';
                                        @endphp
                                        <span class="badge {{ $badgeClass }} small">{{ strtoupper($p->status) }}</span>
                                    </td>
                                    <td class="text-end pe-3">
                                        @if($p->status == 'signed')
                                            <a href="{{ route('admin.orders.pdf', $order) }}" target="_blank" class="btn btn-sm btn-danger rounded-circle">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                        @else
                                            <i class="bi bi-lock text-muted"></i>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-5 text-center text-muted italic">
                                        <i class="bi bi-exclamation-circle d-block mb-2 fs-4"></i>
                                        No se han generado prescripciones para esta orden.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bloque Derecho: Info Paciente --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 fw-bold">
                    <i class="bi bi-person-lines-fill me-2 text-primary"></i>Ficha de Identidad
                </div>
                <div class="card-body">
                    @if($order->patient)
                        <div class="text-center mb-3">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                                <i class="bi bi-person text-primary fs-2"></i>
                            </div>
                            <h6 class="fw-bold mb-0">{{ $order->patient->full_name }}</h6>
                            <small class="text-muted">{{ $order->patient->rut }}</small>
                        </div>
                        <div class="small border-top pt-3 mt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Previsión:</span>
                                <span class="fw-bold">{{ $order->patient->prevision ?? 'Particular' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Monto Pagado:</span>
                                <span class="fw-bold text-success">${{ number_format($order->amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Celular:</span>
                                <span class="fw-bold">{{ $order->patient->phone ?? 'N/A' }}</span>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-person-x text-danger fs-1"></i>
                            <p class="text-danger fw-bold mt-2">Cuenta Eliminada</p>
                            <small class="text-muted italic">El paciente eliminó su perfil del sistema.</small>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 fw-bold small text-uppercase">
                    <i class="bi bi-clock-history me-2 text-secondary"></i>Log de Auditoría
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        @forelse($order->activities ?? [] as $activity)
                            <li class="list-group-item border-0 pb-3">
                                <div class="fw-bold text-dark"><i class="bi bi-record-circle me-1 text-primary"></i> {{ $activity->description }}</div>
                                <div class="text-muted ms-3 small">{{ $activity->created_at->format('d/m/y H:i') }} hrs</div>
                            </li>
                        @empty
                            <li class="list-group-item border-0 text-muted italic">Sin actividad registrada.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
