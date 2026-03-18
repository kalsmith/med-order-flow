{{-- FOOTER TRANSVERSAL --}}
<footer class="pt-5 pb-4 bg-dark text-white">
    <div class="container">
        <div class="row g-4 mb-5">
            <div class="col-lg-4">
                {{-- Logo dinámico: Si tienes el logo lo usamos, si no, el nombre del env --}}
                <div class="mb-4">
                    <img src="{{ asset('assets/logo/logo.png') }}"
                         alt="{{ config('app.name') }}"
                         height="45"
                         class="d-inline-block mb-3 brightness-0 invert">
                    {{-- El estilo brightness/invert es por si el logo es oscuro y el footer es negro --}}
                </div>

                <p class="text-white-50 small">
                    {{ config('app.name') }}: Soluciones médicas digitales para un Chile más sano.
                    Obtén tus órdenes de exámenes preventivos de forma legal y segura.
                </p>

                <div class="d-flex gap-3 mt-4">
                    <a href="#" class="text-white fs-5 opacity-75 hover-opacity-100"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-white fs-5 opacity-75 hover-opacity-100"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h6 class="fw-bold mb-4 text-uppercase small text-primary">Servicios</h6>
                <ul class="list-unstyled small text-white-50">
                    <li class="mb-2">
                        <a href="{{ url('/#packs') }}" class="text-decoration-none text-reset hover-white">Packs Preventivos</a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ url('/#individuales') }}" class="text-decoration-none text-reset hover-white">Exámenes Individuales</a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ url('/#orden-a-medida') }}" class="text-decoration-none text-reset hover-white">Orden a Medida</a>
                    </li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h6 class="fw-bold mb-4 text-uppercase small text-primary">Soporte</h6>
                <ul class="list-unstyled small text-white-50">
                    @foreach($faqs as $item)
                        <li class="mb-2">
                            <a href="{{ route('legal.show', $item->slug) }}" class="text-decoration-none text-reset hover-white">
                                {{ $item->question }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="col-lg-4">
                <h6 class="fw-bold mb-4 text-uppercase small text-primary">Contacto</h6>
                <ul class="list-unstyled small text-white-50">
                    {{-- Cambiado a contacto genérico basado en el dominio --}}
                    <li class="mb-2"><i class="bi bi-envelope me-2 text-primary"></i> contacto@pidetuexamen.cl</li>
                    <li class="mb-2"><i class="bi bi-whatsapp me-2 text-primary"></i> +56 9 1234 5678</li>
                    <li class="mb-2"><i class="bi bi-geo-alt me-2 text-primary"></i> Santiago, Chile</li>
                </ul>
            </div>
        </div>

        <div class="border-top border-secondary pt-4 text-center">
            <p class="small text-white-50 mb-0">
                © {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
            </p>
        </div>
    </div>
</footer>

<style>
    .hover-white:hover { color: white !important; transition: 0.2s; }
    .brightness-0 { filter: brightness(0); }
    .invert { filter: invert(1); } /* Esto vuelve el logo blanco para el fondo oscuro */
</style>
