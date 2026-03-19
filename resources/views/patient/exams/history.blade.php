@extends('layouts.front')

@section('content')
<div class="container py-5" style="max-width: 900px;">
    <div class="d-flex align-items-center mb-4">
        <div class="bg-primary text-white rounded-circle p-3 me-3">
            <i class="bi bi-journal-medical fs-4"></i>
        </div>
        <div>
            <h2 class="fw-bold mb-0">Mi Historial</h2>
            <p class="text-muted mb-0">Gestiona tus exámenes y vuelve a solicitarlos con un clic</p>
        </div>
    </div>

    @forelse($orders as $order)
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">
            <div class="card-header bg-white border-0 p-4 pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-light text-primary border border-primary-subtle rounded-pill px-3 mb-2">
                            Orden #{{ substr($order->id, 0, 8) }}
                        </span>
                        <h5 class="fw-bold text-dark mb-1">{{ $order->patient->name }}</h5>
                        <p class="small text-muted mb-0">
                            <i class="bi bi-calendar3 me-1"></i> {{ $order->created_at->format('d/m/Y') }}
                        </p>
                    </div>

                    {{-- Botón para Volver a Solicitar (Checkout directo) --}}
                    <div>
                        @php
                            $reorderUrl = '#';
                            if($order->type == 'pack') {
                                $reorderUrl = route('order.flow', ['type' => 'pack', 'id' => $order->exam_type_id]);
                            } elseif($order->items->count() > 1) {
                                $ids = $order->items->pluck('exam_type_id')->filter()->implode(',');
                                $reorderUrl = route('order.flow', ['type' => 'multiple']) . '?ids=' . $ids;
                            } else {
                                $reorderUrl = route('order.flow', ['type' => 'exam', 'id' => $order->exam_type_id]);
                            }
                        @endphp
                        <a href="{{ $reorderUrl }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">
                            <i class="bi bi-arrow-repeat me-1"></i> Volver a solicitar
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="bg-light p-3 border-start border-primary border-4 rounded-3 mb-4">
                    <p class="small text-uppercase fw-bold text-muted mb-2">Exámenes solicitados:</p>
                    <div class="row">
                        @foreach($order->items as $item)
                            <div class="col-md-6 mb-1">
                                <i class="bi bi-check2 text-primary me-2"></i>
                                <span class="fw-medium text-secondary">{{ $item->exam_name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    @if($order->activePrescription)
                        <a href="{{ route('orders.download', $order) }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                            <i class="bi bi-download me-2"></i> Descargar Orden Médica (PDF)
                        </a>
                    @else
                        <span class="text-muted small align-self-center me-3">
                            <i class="bi bi-info-circle me-1"></i> Documento en preparación
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-5 bg-white rounded-4 shadow-sm">
            <i class="bi bi-clipboard2-x opacity-25 display-1"></i>
            <p class="mt-4 text-muted fs-5">Aún no tienes exámenes en tu historial.</p>
            <a href="/" class="btn btn-primary rounded-pill px-5 mt-2">Solicitar nuevo examen</a>
        </div>
    @endforelse
</div>
@endsection
