

        {{-- 1. PACKS PREVENTIVOS --}}
        <h3 class="fw-bold mb-4"><i class="bi bi-collection-fill text-primary"></i> Packs Preventivos</h3>

@php $limit = 4; @endphp

<div class="row g-4 mb-5">
    @foreach($packs as $pack)
    <div class="col-lg-4 col-md-6">
        <div class="card card-pack p-4 shadow-sm border-0 h-100 rounded-4 d-flex flex-column transition-hover">

            {{-- Header: Badges y Link Informativo --}}
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <span class="badge {{ $loop->first ? 'bg-primary' : 'bg-secondary bg-opacity-10 text-secondary' }} rounded-pill px-3 py-2">
                    {{ $loop->first ? 'MÁS SOLICITADO' : 'PACK RECOMENDADO' }}
                </span>

                @if($pack->post_id && $pack->post)
                    <a href="{{ route('blog.show', $pack->post->slug) }}"
                       class="info-pill text-decoration-none shadow-sm"
                       title="Leer guía médica sobre este examen">
                        <span class="info-pill-icon">
                            <i class="bi bi-journal-medical"></i>
                        </span>
                        <span class="info-pill-text">Más Información</span>
                    </a>
                @endif
            </div>

            <h4 class="fw-bold mb-1 text-dark">{{ $pack->name }}</h4>

            @if($pack->description)
                <p class="text-muted small mb-3 italic">
                    {{ $pack->description }}
                </p>
            @endif

            {{-- Lista de Exámenes (Chips) --}}
            <div class="flex-grow-1 mb-4 text-start">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($pack->children->take($limit) as $child)
                        <div class="exam-chip bg-white border small px-2 py-1 rounded-3">
                            <i class="bi bi-check-circle-fill text-success"></i> {{ $child->name }}
                        </div>
                    @endforeach

                    @if($pack->children->count() > $limit)
                        @php
                            $remainingExams = $pack->children->slice($limit)->pluck('name')->implode(', ');
                        @endphp
                        <div class="exam-chip bg-light border small px-2 py-1 rounded-3 text-secondary fw-bold"
                             data-bs-toggle="tooltip"
                             data-bs-placement="top"
                             title="{{ $remainingExams }}"
                             style="cursor: help;">
                            + {{ $pack->children->count() - $limit }} más
                        </div>
                    @endif
                </div>
            </div>

            {{-- Footer: Precio y Call to Action --}}
            <div class="pt-4 border-top mt-auto">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="text-muted small d-block mb-0">Total Pack</span>
                        @if($pack->post_id && $pack->post)
                            <a href="{{ route('blog.show', $pack->post->slug) }}" class="info-link-sub">
                                <i class="bi bi-file-earmark-text me-1"></i>Ver detalles
                            </a>
                        @endif
                    </div>
                    <div class="text-end">
                        <span class="price-text fs-3 fw-bold text-primary">${{ number_format($pack->base_price, 0, ',', '.') }}</span>
                    </div>
                </div>

                <a href="{{ route('order.flow', ['type' => 'pack', 'id' => $pack->id]) }}"
                   class="btn btn-primary w-100 py-3 fw-bold shadow-sm rounded-3 btn-buy">
                    Seleccionar Pack <i class="bi bi-chevron-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>

<style>
    /* Efecto de Elevación de la Card */
    .transition-hover {
        transition: all 0.3s ease;
        border: 1px solid transparent !important;
    }
    .transition-hover:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.12) !important;
        border-color: rgba(13, 110, 253, 0.2) !important;
    }

    /* Estilo del Badge Informativo (Cápsula) */
    .info-pill {
        background: rgba(13, 110, 253, 0.06);
        border: 1px solid rgba(13, 110, 253, 0.15);
        border-radius: 50px;
        padding: 3px 10px 3px 4px;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }
    .info-pill-icon {
        background: #0d6efd;
        color: white;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-right: 6px;
        font-size: 0.7rem;
    }
    .info-pill-text {
        font-size: 0.65rem;
        font-weight: 800;
        color: #0d6efd;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .info-pill:hover {
        background: #0d6efd;
        transform: scale(1.05);
    }
    .info-pill:hover .info-pill-text { color: white; }
    .info-pill:hover .info-pill-icon { background: white; color: #0d6efd; }

    /* Link secundario debajo del precio */
    .info-link-sub {
        font-size: 0.72rem;
        color: #6c757d;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s;
    }
    .info-link-sub:hover { color: #0d6efd; text-decoration: underline; }

    /* Botón de compra animado */
    .btn-buy { transition: all 0.3s ease; }
    .btn-buy:hover { letter-spacing: 0.5px; filter: brightness(1.1); }
</style>
