<div> {{-- 1. RAÍZ DEL COMPONENTE --}}

    {{-- Buscador --}}
    <div class="row justify-content-center mb-5">
        <div class="col-md-10 col-lg-8">
            <div class="input-group input-group-lg shadow rounded-pill overflow-hidden border">
                <span class="input-group-text bg-white border-0 ps-4">
                    <i class="bi bi-search text-primary"></i>
                </span>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="form-control border-0 py-3 px-2 shadow-none"
                       placeholder="Escribe el nombre del examen (ej: Hemograma, Perfil, VIH...)">
            </div>
        </div>
    </div>

    {{-- Grilla de Resultados (3 por fila en pantallas grandes) --}}
    <div class="row g-4 justify-content-center">
        @forelse($exams as $exam)
            @php
                $isPack = $exam->children->count() > 0;
            @endphp

            {{-- CAMBIO AQUÍ: col-xl-4 hace que quepan 3 por fila --}}
            <div class="col-12 col-md-6 col-xl-4 animate__animated animate__fadeInUp">
                <div class="card h-100 shadow-sm border-0 rounded-4 transition-hover overflow-hidden"
                     style="border-top: 6px solid {{ $isPack ? '#6610f2' : ($exam->parents->count() > 0 ? '#0dcaf0' : '#0d6efd') }} !important;">

                    <div class="card-body p-4 d-flex flex-column">
                        {{-- Cabecera --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="fw-bold mb-0 text-dark">{{ $exam->name }}</h5>
                                @if($isPack)
                                    <span class="badge bg-primary rounded-pill ms-2" style="font-size: 0.65rem;">PACK</span>
                                @endif
                            </div>
                        </div>

                        {{-- Contenido Variable --}}
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

                        {{-- Precio y Acción --}}
                        <div class="pt-3 border-top mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="d-block text-muted text-uppercase" style="font-size: 0.6rem;">Precio</small>
                                    <span class="text-primary fw-bold fs-4">${{ number_format($exam->base_price, 0, ',', '.') }}</span>
                                </div>

                                <a href="{{ route('order.flow', ['type' => $isPack ? 'pack' : 'exam', 'id' => $exam->id]) }}"
                                   class="btn {{ $isPack ? 'btn-dark' : 'btn-primary' }} rounded-pill px-4 fw-bold shadow-sm">
                                    Pedir
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @empty
            @if(strlen($search) > 2)
                <div class="col-12 text-center py-5">
                    <div class="bg-light d-inline-block p-4 rounded-circle mb-3">
                        <i class="bi bi-search-heart fs-1 text-muted"></i>
                    </div>
                    <h5 class="fw-bold text-dark">No encontramos "{{ $search }}"</h5>
                    <p class="text-muted">Prueba con otro término.</p>
                </div>
            @endif
        @endforelse
    </div>

    <style>
        .transition-hover {
            transition: all 0.3s ease;
        }
        .transition-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 1rem 2rem rgba(0,0,0,.1) !important;
        }
    </style>

</div>
