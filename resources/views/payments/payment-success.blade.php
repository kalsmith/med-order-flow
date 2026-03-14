@extends('layouts.front')

@section('title', '¡Pago Exitoso! - ' . config('app.name'))

@push('styles')
<style>
    .card-success {
        border-radius: 28px;
        border: none;
        box-shadow: 0 15px 35px rgba(0,0,0,0.05);
    }
    .status-icon {
        width: 80px;
        height: 80px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #d1e7dd; /* success-subtle alternative */
        color: #0f5132;
        border-radius: 50%;
        margin-bottom: 1.5rem;
    }
    .detail-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
    }

    /* Optimización para impresión */
    @media print {
        .navbar, .btn, footer, .no-print { display: none !important; }
        body { background: white; }
        .container { margin: 0; padding: 0; }
        .card-success { box-shadow: none; border: 1px solid #eee; }
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <div class="card card-success p-4 p-md-5">
                <div class="card-body text-center">

                    <div class="status-icon">
                        <i class="bi bi-check-lg fs-1"></i>
                    </div>

                    <h2 class="fw-800 mb-2">¡Pago Confirmado!</h2>
                    <p class="text-muted mb-4">
                        Hemos recibido tu pago correctamente. <br>
                        <span class="small">Tu orden está siendo validada por nuestro equipo médico.</span>
                    </p>

                    <div class="detail-box p-4 text-start mb-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-uppercase small text-secondary">
                            Detalle de Transacción
                        </h6>
                        <dl class="row mb-0 small">
                            <dt class="col-6 text-muted fw-normal">Nº de Orden:</dt>
                            <dd class="col-6 text-end fw-bold">#{{ $order->id ?? 'N/A' }}</dd>

                            <dt class="col-6 text-muted fw-normal">Fecha:</dt>
                            <dd class="col-6 text-end">{{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</dd>

                            <dt class="col-6 text-muted fw-normal">Monto Pagado:</dt>
                            <dd class="col-6 text-end text-primary fw-bold fs-6">$ {{ number_format($order->amount ?? 0, 0, ',', '.') }}</dd>

                            <dt class="col-6 text-muted fw-normal">Paciente:</dt>
                            <dd class="col-6 text-end text-truncate">{{ auth()->user()->name }}</dd>
                        </dl>
                    </div>

                    {{-- Acciones --}}
                    <div class="d-grid gap-2 no-print">
                        <a href="{{ route('patient.orders') }}" class="btn btn-primary btn-lg fw-bold shadow-sm">
                            <i class="bi bi-file-earmark-medical me-2"></i> Ir a Mis Órdenes
                        </a>
                        <button onclick="window.print()" class="btn btn-light btn-lg fw-bold border text-muted">
                            <i class="bi bi-printer me-2"></i> Imprimir Comprobante
                        </button>
                    </div>

                    <div class="mt-4 no-print">
                        <p class="small text-muted mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Recibirás un correo cuando tu orden esté lista.
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
