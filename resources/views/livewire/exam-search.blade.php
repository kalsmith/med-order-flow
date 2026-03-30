<div class="position-relative"> {{-- 1. RAÍZ DEL COMPONENTE --}}

    {{-- Buscador y Alerta de Límite --}}
    <div class="row justify-content-center mb-5">
        <div class="col-md-10 col-lg-8 text-center">
            <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border bg-white">
                <span class="input-group-text bg-white border-0 ps-4">
                    <i class="bi bi-search text-primary"></i>
                </span>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="form-control border-0 py-3 px-2 shadow-none"
                       placeholder="Escribe el nombre del examen (ej: Hemograma, Perfil, VIH...)">
            </div>

            <div class="mt-3 animate__animated animate__fadeIn" style="min-height: 25px;">
                @if(count($selectedExams) >= $maxExams)
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Has alcanzado el máximo de {{ $maxExams }} exámenes permitidos.
                    </span>
                @elseif(count($selectedExams) > 0)
                    <small class="text-primary fw-bold bg-white px-3 py-1 rounded-pill shadow-sm border">
                        <i class="bi bi-info-circle me-1"></i> {{ count($selectedExams) }} de {{ $maxExams }} seleccionados.
                    </small>
                @endif
            </div>
        </div>
    </div>

    {{-- Grilla de Resultados --}}
    <div class="row g-4 justify-content-center mb-5">
        @forelse($exams as $exam)
            @php
                $isPack = $exam->children->count() > 0;
                $isSelected = isset($selectedExams[$exam->id]);
                $limitReached = count($selectedExams) >= $maxExams;
                $accentColor = $isPack ? '#6610f2' : ($exam->parents->count() > 0 ? '#0dcaf0' : '#0d6efd');
                $isLongName = strlen($exam->name) > 70;
            @endphp

            <div class="col-12 col-md-6 col-lg-4 col-xl-3 animate__animated animate__fadeInUp">
                <div class="card h-100 border-0 rounded-4 shadow-sm custom-exam-card {{ $isSelected ? 'card-selected' : '' }}">

                    <div class="card-accent-bar" style="background-color: {{ $accentColor }};"></div>

                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            {{-- Ajuste dinámico de fuente para nombres largos --}}
                            <h6 class="fw-bold text-dark mb-0 exam-title {{ $isLongName ? 'title-long' : '' }}" title="{{ $exam->name }}">
                                {{ $exam->name }}
                            </h6>
                            @if($isPack)
                                <span class="badge rounded-pill pack-badge shadow-sm ms-2">PACK</span>
                            @endif
                        </div>

                        <div class="flex-grow-1">
                            @if($isPack)
                                <div class="inclusion-box p-3 rounded-3 mb-3">
                                    <span class="inclusion-label">INCLUYE:</span>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($exam->children->take(3) as $child)
                                            <li class="inclusion-item text-truncate small">
                                                <i class="bi bi-check2 text-primary me-1"></i> {{ $child->name }}
                                            </li>
                                        @endforeach
                                        @if($exam->children->count() > 3)
                                            <li class="inclusion-more text-primary fw-bold mt-1 small">+{{ $exam->children->count() - 3 }} más...</li>
                                        @endif
                                    </ul>
                                </div>
                            @elseif($exam->parents->count() > 0)
                                <div class="promo-box p-2 mb-3 text-center rounded-3 small fw-bold">
                                    <i class="bi bi-gift-fill me-1"></i> Disponible en Pack
                                </div>
                            @else
                                <p class="text-muted small mb-3">Análisis clínico individual de alta precisión.</p>
                            @endif
                        </div>

                        <div class="mt-auto pt-3 border-top">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="price-container">
                                    <span class="price-symbol small fw-bold text-primary">$</span>
                                    <span class="price-amount fs-4 fw-extrabold text-primary">{{ number_format($exam->base_price, 0, ',', '.') }}</span>
                                </div>

                                @if($isPack)
                                    <a href="{{ route('order.flow', ['type' => 'pack', 'id' => $exam->id]) }}"
                                       class="btn btn-dark btn-sm rounded-pill px-3 fw-bold shadow-sm">
                                        Pedir Pack
                                    </a>
                                @else
                                    <a href="{{ route('order.flow', ['type' => 'exam', 'id' => $exam->id]) }}"
                                       class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-sm">
                                        Solicitar
                                    </a>
                                @endif
                            </div>

                            @if(!$isPack)
                                <button wire:click="toggleExam({{ $exam->id }}, '{{ $exam->name }}')"
                                        @if($limitReached && !$isSelected) disabled @endif
                                        class="btn btn-sm w-100 rounded-pill mt-2 fw-bold transition-all {{ $isSelected ? 'btn-success' : 'btn-outline-primary' }}"
                                        style="font-size: 0.75rem;">
                                    @if($isSelected)
                                        <i class="bi bi-check-lg me-1"></i> Añadido a mi lista
                                    @elseif($limitReached)
                                        <i class="bi bi-lock-fill me-1"></i> Límite alcanzado
                                    @else
                                        <i class="bi bi-plus-lg me-1"></i> Añadir a lista múltiple
                                    @endif
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            @if(strlen($search) > 2)
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search text-muted fs-1 d-block mb-3"></i>
                    <p class="text-muted">No encontramos resultados para "{{ $search }}"</p>
                </div>
            @endif
        @endforelse
    </div>

    {{-- BARRA FLOTANTE OPTIMIZADA --}}
    @if(count($selectedExams) > 0)
        <div style="height: 120px;"></div>
        <div class="fixed-bottom bg-white border-top shadow-lg animate__animated animate__slideInUp" style="z-index: 1050; padding-bottom: env(safe-area-inset-bottom);">
            <div class="container py-3">
                <div class="row align-items-center">
                    <div class="col-md-7 d-none d-md-block">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm fw-bold flex-shrink-0" style="width: 45px; height: 45px;">
                                {{ count($selectedExams) }}
                            </div>
                            <div class="overflow-hidden">
                                <h6 class="fw-bold mb-0 text-dark small">Lista para Orden Múltiple</h6>
                                <p class="small text-muted mb-0 text-truncate">
                                    {{-- Usamos Str::limit para que la lista no rompa el contenedor --}}
                                    @foreach($selectedExams as $id => $name)
                                        <span class="text-dark">{{ Str::limit($name, 30) }}</span>@if(!$loop->last), @endif
                                    @endforeach
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-5">
                        <div class="d-flex align-items-center gap-2">
                            <button wire:click="clearSelection" class="btn btn-sm btn-link text-danger text-decoration-none fw-bold">Limpiar</button>
                            <a href="{{ $orderUrl }}" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold flex-grow-1 shadow-sm fs-6 text-truncate">
                                Solicitar Grupo <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .custom-exam-card {
            transition: all 0.3s cubic-bezier(.25,.8,.25,1);
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.05) !important;
        }

        .custom-exam-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.1) !important;
        }

        .card-accent-bar { height: 4px; width: 100%; }

        /* MANEJO DE TÍTULOS LARGOS */
        .exam-title {
            font-size: 0.95rem;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 4; /* Permite hasta 4 líneas */
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 3.8rem; /* Mantiene las tarjetas alineadas */
            word-break: break-word;
        }

        /* Si el nombre es muy largo, bajamos un poco el tamaño */
        .title-long {
            font-size: 0.85rem !important;
        }

        .custom-exam-card .card-body {
            padding: 1.25rem !important;
        }

        .inclusion-box { background-color: #f8f9fa; border: 1px solid rgba(0,0,0,0.02); }
        .inclusion-label { font-size: 0.65rem; font-weight: 800; color: #adb5bd; letter-spacing: 0.5px; display: block; margin-bottom: 5px; }
        .inclusion-item { color: #555; }

        .card-selected {
            border: 2px solid #198754 !important;
            background-color: #f8fff9 !important;
        }

        .pack-badge { background-color: #6610f2; font-size: 0.6rem; padding: 4px 8px; color: white; }
        .promo-box { background-color: rgba(13, 202, 240, 0.1); color: #087990; border: 1px dashed rgba(13, 202, 240, 0.3); }
        .fw-extrabold { font-weight: 800; }

        @media (max-width: 767px) {
            .fixed-bottom { padding: 0 10px; }
            .exam-title { -webkit-line-clamp: 3; min-height: 3rem; }
        }
    </style>
</div>
