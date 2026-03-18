<div class="position-relative"> {{-- 1. RAÍZ DEL COMPONENTE --}}

    {{-- Buscador --}}
    <div class="row justify-content-center mb-5">
        <div class="col-md-10 col-lg-8 text-center">
            <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border">
                <span class="input-group-text bg-white border-0 ps-4">
                    <i class="bi bi-search text-primary"></i>
                </span>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="form-control border-0 py-3 px-2 shadow-none"
                       placeholder="Escribe el nombre del examen (ej: Hemograma, Perfil, VIH...)">
            </div>
            @if(count($selectedExams) > 0)
                <small class="text-primary fw-bold d-block mt-3 animate__animated animate__fadeIn">
                    <i class="bi bi-info-circle me-1"></i> Tienes {{ count($selectedExams) }} seleccionados. Puedes seguir añadiendo.
                </small>
            @endif
        </div>
    </div>

    {{-- Grilla de Resultados --}}
    <div class="row g-4 justify-content-center mb-5">
        @forelse($exams as $exam)
            @php
                $isPack = $exam->children->count() > 0;
                $isSelected = isset($selectedExams[$exam->id]);
            @endphp

            <div class="col-12 col-md-6 col-xl-4 animate__animated animate__fadeInUp">
                <div class="card h-100 shadow-sm border-0 rounded-4 transition-hover overflow-hidden {{ $isSelected ? 'ring-active' : '' }}"
                     style="border-top: 6px solid {{ $isPack ? '#6610f2' : ($exam->parents->count() > 0 ? '#0dcaf0' : '#0d6efd') }} !important;">

                    <div class="card-body p-4 d-flex flex-column">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="fw-bold mb-0 text-dark">{{ $exam->name }}</h5>
                                @if($isPack)
                                    <span class="badge bg-primary rounded-pill ms-2" style="font-size: 0.65rem;">PACK</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex-grow-1 mb-3">
                            @if($isPack)
                                <div class="p-2 bg-light rounded-3">
                                    <small class="text-muted d-block mb-1 fw-bold" style="font-size: 0.65rem;">INCLUYE:</small>
                                    <ul class="list-unstyled mb-0" style="font-size: 0.75rem;">
                                        @foreach($exam->children->take(3) as $child)
                                            <li class="text-truncate"><i class="bi bi-check2 text-success"></i> {{ $child->name }}</li>
                                        @endforeach
                                        @if($exam->children->count() > 3)
                                            <li class="text-primary fw-bold">+{{ $exam->children->count() - 3 }} más...</li>
                                        @endif
                                    </ul>
                                </div>
                            @elseif($exam->parents->count() > 0)
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 w-100 py-2">
                                    <i class="bi bi-gift-fill me-1"></i> Disponible en Pack
                                </span>
                            @else
                                <small class="text-muted">Examen Individual</small>
                            @endif
                        </div>

                        <div class="pt-3 border-top mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-primary fw-bold fs-4">${{ number_format($exam->base_price, 0, ',', '.') }}</span>
                                </div>

                                @if($isPack)
                                    <a href="{{ route('order.flow', ['type' => 'pack', 'id' => $exam->id]) }}"
                                       class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm">
                                        Pedir Pack
                                    </a>
                                @else
                                    <button wire:click="toggleExam({{ $exam->id }}, '{{ $exam->name }}')"
                                            class="btn {{ $isSelected ? 'btn-success' : 'btn-outline-primary' }} rounded-pill px-4 fw-bold shadow-sm transition-all">
                                        @if($isSelected)
                                            <i class="bi bi-check-lg"></i> Añadido
                                        @else
                                            <i class="bi bi-plus-lg"></i> Añadir
                                        @endif
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            @if(strlen($search) > 2)
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search text-muted fs-1 d-block mb-3"></i>
                    <p class="text-muted">No encontramos exámenes que coincidan con "{{ $search }}"</p>
                </div>
            @endif
        @endforelse
    </div>

    {{-- BARRA FLOTANTE --}}
    @if(count($selectedExams) > 0)
        {{-- Espaciador para que el contenido no quede oculto detrás de la barra en Desktop --}}
        <div style="height: 100px;"></div>

        <div class="fixed-bottom bg-white border-top shadow-lg animate__animated animate__slideInUp" style="z-index: 1050; padding-bottom: env(safe-area-inset-bottom);">
            <div class="container py-3">
                <div class="row align-items-center">
                    <div class="col-md-7 d-none d-md-block">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 45px; height: 45px;">
                                <span class="fw-bold">{{ count($selectedExams) }}</span>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Tu Orden Personalizada</h6>
                                <p class="small text-muted mb-0 text-truncate" style="max-width: 450px;">
                                    {{ implode(', ', $selectedExams) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-5">
                        <div class="d-flex align-items-center gap-3">
                            <button wire:click="clearSelection" class="btn btn-link text-danger text-decoration-none small fw-bold">
                                Limpiar
                            </button>
                            {{-- Usamos la propiedad computada $orderUrl del componente --}}
                            <a href="{{ $orderUrl }}"
                               class="btn btn-primary btn-lg rounded-pill px-4 fw-bold flex-grow-1 shadow">
                                Solicitar por $3.990 <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .transition-hover { transition: all 0.3s cubic-bezier(.25,.8,.25,1); }
        .transition-hover:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,.175) !important; }
        .transition-all { transition: all 0.2s ease-in-out; }
        .ring-active {
            border: 2px solid #198754 !important;
            background-color: #f8fff9;
        }
        /* Ajuste para móviles para que la barra no tape el último botón */
        @media (max-width: 767px) {
            .fixed-bottom { padding-left: 10px; padding-right: 10px; }
        }
    </style>

</div>
