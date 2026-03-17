@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">Auditoría Clínica: Orden #{{ substr($order->id, 0, 8) }}</h2>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Volver al listado
        </a>
    </div>

    <div class="row">
        {{-- Bloque Izquierdo: Información del Paciente y Orden --}}
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">Detalles de la Orden</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Paciente</label>
                            <p class="fw-bold">{{ $order->patient->full_name ?? 'N/A' }}</p>

                            <label class="text-muted small d-block">RUT</label>
                            <p>{{ $order->patient->rut ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Examen solicitado</label>
                            <p class="fw-bold">{{ $order->display_name }}</p>

                            <label class="text-muted small d-block">Estado Actual</label>
                            <span class="badge bg-{{ $order->status == 'paid' ? 'primary' : 'success' }}">
                                {{ strtoupper($order->status) }}
                            </span>
                        </div>
                    </div>
                    <hr>
                    <label class="text-muted small d-block">Contexto Clínico (Anamnesis)</label>
                    <p class="bg-light p-3 rounded">{{ $order->clinical_context ?: 'Sin descripción proporcionada.' }}</p>
                </div>
            </div>

            {{-- Historial de Prescripciones / Firmas --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Documentos Médicos Generados</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Correlativo</th>
                                <th>Médico Firmante</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->prescriptions as $p)
                            <tr>
                                <td>{{ $p->correlative_number }}</td>
                                <td>{{ $p->doctor->name }}</td>
                                <td>{{ $p->signed_at ? $p->signed_at->format('d/m/Y H:i') : 'No firmado' }}</td>
                                <td>
                                    <span class="badge {{ $p->status == 'signed' ? 'bg-success' : 'bg-warning' }}">
                                        {{ strtoupper($p->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($p->status == 'signed')
                                    <a href="{{ route('admin.orders.pdf', $order) }}" target="_blank" class="btn btn-sm btn-link">Ver PDF</a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-3">No hay documentos generados aún.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Bloque Derecho: Auditoría y Log --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white fw-bold">Panel de Supervisión (DT)</div>
                <div class="card-body">
                    <p class="small text-muted">Como Director Técnico, puedes anular esta orden si detectas una irregularidad legal o clínica.</p>

                    <button class="btn btn-danger w-100 mb-2" onclick="confirm('¿Estás seguro de invalidar esta orden por razones técnicas?')">
                        <i class="bi bi-x-circle"></i> Anulación Técnica (DT)
                    </button>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold small">Trazabilidad (Activity Log)</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        @foreach($order->activities ?? [] as $activity)
                        <li class="list-group-item">
                            <strong>{{ $activity->description }}</strong><br>
                            <span class="text-muted">{{ $activity->created_at->format('d/m H:i') }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
