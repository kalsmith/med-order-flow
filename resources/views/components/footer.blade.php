{{-- FOOTER TRANSVERSAL --}}
<footer class="pt-5 pb-4 bg-dark text-white">
    <div class="container">
        <div class="row g-4 mb-5">
            <div class="col-lg-4">
                <h4 class="fw-bold text-primary mb-4"><i class="bi bi-droplet-fill"></i> MedOrderFlow</h4>
                <p class="text-white-50 small">Soluciones médicas digitales para un Chile más sano. Obtén tus órdenes de exámenes preventivos de forma legal y segura.</p>
                <div class="d-flex gap-3 mt-4">
                    <a href="#" class="text-white fs-5"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-white fs-5"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h6 class="fw-bold mb-4 text-uppercase small text-primary">Servicios</h6>
                <ul class="list-unstyled small text-white-50">
                    <li class="mb-2"><a href="#packs" class="text-decoration-none text-reset">Packs Preventivos</a></li>
                    <li class="mb-2"><a href="#individuales" class="text-decoration-none text-reset">Exámenes Individuales</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none text-reset">Orden a Medida</a></li>
                </ul>
            </div>

<div class="col-6 col-lg-2">
    <h6 class="fw-bold mb-4 text-uppercase small text-primary">Soporte</h6>
    <ul class="list-unstyled small text-white-50">
        {{-- Enlace estático a la sección de FAQs del home si quieres --}}


        {{-- Renderiza TODO lo que esté activo en la tabla faqs --}}
@foreach($faqs as $item)
    <li class="mb-2">
        {{-- Laravel ahora usará el slug automáticamente gracias a getRouteKeyName --}}
        <a href="{{ route('legal.show', $item) }}" class="text-decoration-none text-reset">
            {{ $item->question }}
        </a>
    </li>
@endforeach
    </ul>
</div>

            <div class="col-lg-4">
                <h6 class="fw-bold mb-4 text-uppercase small text-primary">Contacto</h6>
                <ul class="list-unstyled small text-white-50">
                    <li class="mb-2"><i class="bi bi-envelope me-2 text-primary"></i> contacto@medorderflow.cl</li>
                    <li class="mb-2"><i class="bi bi-whatsapp me-2 text-primary"></i> +56 9 1234 5678</li>
                    <li class="mb-2"><i class="bi bi-geo-alt me-2 text-primary"></i> Santiago, Chile</li>
                </ul>
            </div>
        </div>

        <div class="border-top border-secondary pt-4 text-center">
            <p class="small text-white-50 mb-0">© {{ date('Y') }} MedOrder Flow Chile. Todos los derechos reservados. Registrados en la Superintendencia de Salud.</p>
        </div>
    </div>
</footer>
