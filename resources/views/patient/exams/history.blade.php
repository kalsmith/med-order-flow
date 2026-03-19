@extends('layouts.app')

@section('content')
<div class="container py-5" style="max-width: 800px;">
    <h2 class="fw-bold mb-4"><i class="bi bi-journal-medical text-primary me-2"></i>Mi Historial</h2>

    @forelse($orders as $order)
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">{{ $order->patient->name }}</h5>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-calendar3 me-1"></i> {{ $order->created_at->format('d/m/Y') }}
                        </p>
                    </div>
                    <span class="badge bg-success-soft text-success rounded-pill px-3">
                        <i class="bi bi-check-circle-fill me-1"></i> Pagado
                    </span>
                </div>

                <div class="bg-light p-3 mb-3" style="border-radius: 12px;">
                    <p class="small text-uppercase fw-bold text-muted mb-2">Exámenes en esta orden:</p>
                    <ul class="list-unstyled mb-0">
                        @foreach($order->items as $item)
                            <li class="mb-1 d-flex align-items-center">
                                <i class="bi bi-dot text-primary fs-4"></i>
                                <span class="fw-medium text-dark">{{ $item->exam_name }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="d-grid">
                    @if($order->prescription)
                        {{-- Aquí irá el link a la descarga una vez que el doctor firme --}}
                        <a href="{{ route('orders.download', $order) }}" class="btn btn-primary rounded-pill fw-bold py-2">
                            <i class="bi bi-download me-2"></i> Descargar Orden Médica
                        </a>
                    @else
                        <button class="btn btn-outline-secondary disabled rounded-pill py-2">
                            <i class="bi bi-hourglass-split me-2"></i> Procesando Orden Médica
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-5">
            <p class="text-muted">Aún no tienes exámenes en tu historial.</p>
        </div>
    @endforelse
</div>
@endsection
