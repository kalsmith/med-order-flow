@extends('layouts.kiosko')

@section('content')
<section class="hero-section text-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill mb-3 fw-bold">Rápido · Seguro · Legal</span>
                <h1 class="display-5 fw-bold mb-3">Obtén tu Orden Médica en minutos</h1>
                <p class="lead text-muted mb-4">Selecciona el pack de exámenes que necesitas. Un médico general revisará y firmará tu orden de forma digital para que la lleves a cualquier laboratorio.</p>

                {{-- Alertas de Feedback --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<div class="container py-5">
    <div class="d-flex align-items-center mb-4">
        <h3 class="fw-bold m-0 text-dark">Nuestros Packs Destacados</h3>
        <hr class="flex-grow-1 ms-4 text-muted opacity-25">
    </div>

    <div class="row g-4">
        @foreach($packs as $pack)
        <div class="col-md-4">
            <div class="card h-100 card-pack border-0 shadow-sm">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="mb-3">
                        <span class="badge bg-info-subtle text-info rounded-pill px-3">Batería Completa</span>
                    </div>

                    <h4 class="fw-bold mb-2">{{ $pack->name }}</h4>
                    <p class="text-muted small mb-4">Ideal para {{ strtolower($pack->specialty->name) }}</p>

                    <div class="bg-light rounded-3 p-3 mb-4">
                        <h6 class="small fw-bold text-uppercase text-muted mb-3">Incluye {{ $pack->total_items }} exámenes:</h6>
                        <ul class="list-unstyled mb-0">
                            @foreach($pack->children as $child)
                                <li class="small mb-2 d-flex align-items-start">
                                    <i class="bi bi-check-circle-fill text-primary me-2 mt-1"></i>
                                    <span>{{ $child->name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block text-muted small">Precio Total</span>
                            <span class="price-tag">${{ number_format($pack->base_price, 0, ',', '.') }}</span>
                        </div>
                        {{-- BOTÓN ACTUALIZADO --}}
                        <button class="btn btn-primary shadow-sm px-4 btn-solicitar"
                                data-id="{{ $pack->id }}"
                                data-name="{{ $pack->name }}">
                            Solicitar <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Sección de Exámenes Individuales --}}
    @if(isset($individuales) && $individuales->count() > 0)
    <div class="mt-5 pt-5">
        <h5 class="fw-bold mb-4 text-muted small text-uppercase ls-wide">Exámenes Individuales</h5>
        <div class="row row-cols-1 row-cols-md-3 g-3">
            @foreach($individuales as $item)
            <div class="col">
                <div class="p-3 bg-white border rounded-3 d-flex justify-content-between align-items-center shadow-sm">
                    <span class="fw-medium">{{ $item->name }}</span>
                    {{-- ENLACE ACTUALIZADO --}}
                    <button class="btn btn-link text-primary fw-bold text-decoration-none small btn-solicitar"
                            data-id="{{ $item->id }}"
                            data-name="{{ $item->name }}">
                        Solicitar
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- MODAL --}}
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalPackName text-primary">Solicitar Orden</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('orders.store.public') }}" method="POST">
                @csrf
                <input type="hidden" name="exam_type_id" id="modalExamTypeId">

                <div class="modal-body p-4">
                    <p class="text-muted small mb-4">Ingresa tus datos para generar la orden médica oficial.</p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre Completo</label>
                        <input type="text" name="patient_name" class="form-control" placeholder="Ej: Juan Pérez" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">RUT</label>
                        <input type="text" name="patient_rut" class="form-control" placeholder="12.345.678-9" required>
                        <div class="form-text small">Sin puntos ni guión (ej: 123456789).</div>
                    </div>

                    <div class="alert alert-primary-subtle border-0 small d-flex align-items-center mt-4 mb-0">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Esta orden será revisada y firmada por un Médico General habilitado.
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Confirmar Pedido</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const orderButtons = document.querySelectorAll('.btn-solicitar');
        const modalIdInput = document.getElementById('modalExamTypeId');
        const modalTitle = document.getElementById('modalPackName');
        const orderModalElement = document.getElementById('orderModal');

        // Inicializamos el modal de Bootstrap
        const myModal = new bootstrap.Modal(orderModalElement);

        orderButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const packId = this.getAttribute('data-id');
                const packName = this.getAttribute('data-name');

                modalIdInput.value = packId;
                if(modalTitle) modalTitle.innerText = 'Solicitar: ' + packName;

                myModal.show();
            });
        });
    });
</script>
@endsection
