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
        <div class="col-md-8">
            {{-- Card de Detalles del Examen / Pack / Custom --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                    <span>Contenido de la Orden ({{ ucfirst($order->type) }})</span>
                    <span class="badge bg-{{ $order->status == 'paid' ? 'primary' : 'success' }}">
                        {{ strtoupper($order->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="text-muted small d-block">Examen Solicitado</label>
                            <h4 class="fw-bold text-dark">
                                @if($order->type === 'custom')
                                    <span class="text-primary"><i class="bi bi-pencil-square"></i> Orden Personalizada</span>
                                @else
                                    {{ $order->examType->name ?? 'Examen no encontrado' }}
                                    @if($order->examType?->isProfile())
                                        <span class="badge bg-info text-dark ms-2" style="font-size: 0.5em; vertical-align: middle;">
                                            <i class="bi bi-box-seam"></i> PACK / PERFIL
                                        </span>
                                    @endif
                                @endif
                            </h4>
                        </div>
                    </div>

                    {{-- Lógica para Packs Standard --}}
                    @if($order->type === 'standard' && $order->examType?->isProfile())
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
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Lógica para Órdenes Custom --}}
                    @if($order->type === 'custom')
                        <div class="p-3 border border-primary rounded bg-light mb-3">
                            <label class="text-primary small d-block mb-2 fw-bold text-uppercase">
                                <i class="bi bi-info-circle"></i> Detalle de exámenes solicitados por el paciente:
                            </label>
                            <p class="mb-0 fw-bold text-secondary" style="white-space: pre-line;">{{ $order->custom_description }}</p>
                        </div>
                    @endif

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <label class="text-muted small d-block fw-bold text-uppercase mb-2">Contexto Clínico (Anamnesis)</label>
                            <div class="p-3 bg-light rounded border-start border-4 border-info">
                                {{ $order->clinical_context ?: 'Sin información clínica adicional.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Historial de Prescripciones --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Documentos Médicos y Firmas</div>
                <div class="card-body p-0 text-center">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light text-start">
                                <tr>
                                    <th>Correlativo</th>
                                    <th>Médico Responsable</th>
                                    <th>Fecha Firma</th>
                                    <th>Estado</th>
                                    <th class="text-end">Documento</th>
                                </tr>
                            </thead>
                            <tbody class="text-start">
                                @forelse($order->prescriptions as $p)
                                <tr>
                                    <td class="align-middle fw-bold">#{{ $p->correlative_number }}</td>
                                    <td class="align-middle">
                                        @if($p->doctor)
                                            {{ $p->doctor->user->name }}<br>
                                            <small class="text-muted font-monospace">RNPI: {{ $p->doctor->rnpi_number }}</small>
                                        @else
                                            <span class="text-muted small italic">Pendiente de asignación</span>
                                        @endif
                                    </td>
                                    <td class="align-middle small">
                                        {{ $p->signed_at ? $p->signed_at->format('d/m/Y H:i') : '---' }}
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge {{ $p->status == 'signed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ strtoupper($p->status) }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-end">
                                        @if($p->status == 'signed')
                                        <a href="{{ route('admin.orders.pdf', $order) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-file-earmark-pdf"></i> PDF
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="py-4 text-muted text-center">No se han generado prescripciones.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bloque Derecho --}}
        <div class="col-md-4">
            {{-- Card Paciente --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">Información del Paciente</div>
                <div class="card-body">
                    <h6 class="mb-1 fw-bold">{{ $order->patient->full_name ?? 'N/A' }}</h6>
                    <p class="text-muted small mb-3">RUT: {{ $order->patient->rut ?? 'N/A' }}</p>
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Previsión:</span> <span class="fw-bold">{{ $order->patient->prevision ?? 'N/A' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Activity Log --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold small">Log de Eventos</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        @forelse($order->activities ?? [] as $activity)
                        <li class="list-group-item border-0">
                            <p class="mb-0 fw-bold">{{ $activity->description }}</p>
                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                        </li>
                        @empty
                        <li class="list-group-item text-muted text-center italic">Sin registros.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
