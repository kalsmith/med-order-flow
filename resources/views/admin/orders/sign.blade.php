@extends('layouts.admin')

@section('header', 'Firma de Orden Médica')

@section('header-actions')
    <div class="d-flex gap-2">
        <form action="{{ route('admin.orders.release', ['medical_order' => $order->id]) }}" method="POST">
            @csrf
            <input type="hidden" name="redirect_to" value="index">
            <button type="submit" class="btn btn-outline-secondary btn-sm shadow-sm border-0">
                <i class="bi bi-arrow-left me-1"></i> Liberar y Volver
            </button>
        </form>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        {{-- COLUMNA IZQUIERDA: Datos y Firma --}}
        <div class="col-xl-8 col-lg-7">

            {{-- Alerta de Tiempo --}}
            @php
                $expiresAt = $order->claimed_at ? $order->claimed_at->addMinutes(20) : now()->addMinutes(20);
                $minutesLeft = max(0, now()->diffInMinutes($expiresAt, false));
            @endphp

            <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-warning" role="progressbar"
                         style="width: {{ ($minutesLeft / 20) * 100 }}%"></div>
                </div>
                <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center bg-light">
                    <small class="text-muted fw-bold"><i class="bi bi-clock-history me-1"></i> TIEMPO DE RESERVA</small>
                    <span class="badge bg-white text-dark border shadow-sm px-3">
                        Expira en <span id="timer">{{ ceil($minutesLeft) }}</span> min
                    </span>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-800 text-dark">
                        <i class="bi bi-person-badge text-primary me-2"></i>Paciente: {{ $order->patient->full_name }}
                    </h5>
                    <span class="badge bg-primary-subtle text-primary border-primary-subtle px-3 py-2">
                        ID: {{ strtoupper(substr($order->id, 0, 8)) }}
                    </span>
                </div>

                <div class="card-body p-4">
                    {{-- Info del Paciente --}}
                    <div class="row mb-4 bg-light p-3 rounded-3 g-3">
                        <div class="col-sm-4">
                            <label class="text-muted small text-uppercase fw-bold d-block">RUT</label>
                            <span class="fw-bold">{{ $order->patient->rut }}</span>
                        </div>
                        <div class="col-sm-4">
                            <label class="text-muted small text-uppercase fw-bold d-block">Edad</label>
                            <span>{{ $order->patient->age ?? 'N/A' }} años</span>
                        </div>
                        <div class="col-sm-4">
                            <label class="text-muted small text-uppercase fw-bold d-block">Tipo de Orden</label>
                            <span class="badge bg-dark">{{ $order->examType->name ?? 'SOLICITUD ESPECIAL' }}</span>
                        </div>
                    </div>

                    {{-- Formulario de Firma --}}
                    <form action="{{ route('admin.orders.sign.process', ['medical_order' => $order->id]) }}"
                          method="POST" id="signature-form">
                        @csrf
                        <div class="form-group mb-4">
                            <label class="text-dark fw-bold mb-2 d-flex justify-content-between">
                                <span><i class="bi bi-pencil-fill me-1 text-primary"></i> INDICACIÓN MÉDICA</span>
                                <small class="text-muted">Esta información aparecerá en el PDF</small>
                            </label>
                            <textarea name="clinical_context"
                                      class="form-control border-2 shadow-none focus-ring"
                                      rows="8"
                                      placeholder="Ej: Paciente presenta cuadro de... se solicita examen para descartar..."
                                      style="--bs-focus-ring-color: rgba(var(--bs-primary-rgb), .1)"
                                      required>{{ old('clinical_context', $order->clinical_context) }}</textarea>
                        </div>
                    </form>
                </div>

                <div class="card-footer bg-white p-4 border-top">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        {{-- SELLO --}}
                        <div class="d-flex align-items-center gap-3 border-end pe-4">
                            <img src="{{ auth()->user()->doctor->signature_path ? asset('storage/' . auth()->user()->doctor->signature_path) : asset('images/no-signature.png') }}"
                                 alt="Firma" style="height: 60px;" class="opacity-75">
                            <div>
                                <div class="small text-muted">Sello del Profesional</div>
                                <div class="fw-bold">Dr. {{ auth()->user()->name }}</div>
                            </div>
                        </div>

                        {{-- ACCIONES --}}
                        <div class="d-flex gap-2">
                            @if(!$order->exam_type_id)
                                <button type="button" class="btn btn-outline-info border-0" data-bs-toggle="modal" data-bs-target="#derivateModal">
                                    <i class="bi bi-person-gear"></i> Derivar
                                </button>
                            @endif
                            <button type="button" class="btn btn-outline-danger border-0" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="bi bi-x-circle"></i> Rechazar
                            </button>
                            <button type="submit" form="signature-form" class="btn btn-success btn-lg px-5 shadow-sm fw-bold">
                                <i class="bi bi-check-all me-2"></i> FIRMAR ORDEN
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: Chat / Interacciones --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px; height: calc(100vh - 100px);">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-chat-dots-fill text-primary me-2"></i>
                        Chat con Paciente
                    </h6>
                </div>
                <div class="card-body p-0 d-flex flex-column" style="height: 100%;">
                    {{-- Componente Livewire de Chat --}}
                    @livewire('admin.order-interactions', ['order' => $order])
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.orders.partials.modals-sign')

<style>
    .fw-800 { font-weight: 800; }
    .focus-ring:focus { border-color: #0d6efd !important; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, .15) !important; }
    .card-order-chat { border-radius: 15px; }
    /* Ajuste para que el chat use el alto disponible */
    #chat-container-livewire { flex-grow: 1; overflow-y: hidden; }
</style>

<script>
    // Simple timer update
    setInterval(() => {
        const timerEl = document.getElementById('timer');
        let val = parseInt(timerEl.innerText);
        if (val > 0) timerEl.innerText = val - 1;
    }, 60000);
</script>
@endsection
