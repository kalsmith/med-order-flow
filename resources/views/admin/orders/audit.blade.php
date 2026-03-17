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
                <div class="card-header bg-dark text-white fw-bold">Resolución y Documentos Médicos</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Folio</th>
                                    <th>Médico</th>
                                    <th>Indicación Clínica/Validación</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->prescriptions as $p)
                                <tr>
                                    <td class="align-middle">
                                        <span class="fw-bold">#{{ $p->correlative_number }}</span><br>
                                        <small class="text-muted">{{ $p->created_at->format('d/m/y') }}</small>
                                    </td>
                                    <td class="align-middle">
                                        @if($p->doctor)
                                            <span class="fw-bold">{{ $p->doctor->prefix }} {{ $p->doctor->user->name }}</span><br>
                                            <small class="text-muted">RNPI: {{ $p->doctor->rnpi_number }}</small>
                                        @else
                                            <span class="badge bg-light text-warning border border-warning">Pendiente de Médico</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        {{-- Aquí mostramos lo que el médico escribió o validó --}}
                                        @if($p->type === 'custom')
                                            <div class="small bg-light p-2 rounded border">
                                                <strong>Indicación Médica:</strong><br>
                                                {{ $p->clinical_context ?: 'El médico validó la solicitud original sin observaciones adicionales.' }}
                                            </div>
                                        @else
                                            <small class="text-muted">Validación de Examen Estándar</small>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge {{ $p->status == 'signed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ strtoupper($p->status) }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-end">
                                        @if($p->status == 'signed')
                                            <a href="{{ route('admin.orders.pdf', $order) }}" target="_blank" class="btn btn-sm btn-danger">
                                                <i class="bi bi-file-earmark-pdf"></i> Ver PDF
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="py-5 text-center text-muted">No se han generado prescripciones para esta orden.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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
