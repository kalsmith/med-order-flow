        {{-- BUSCADOR LIVEWIRE --}}
        <div class="bg-white p-4 p-md-5 rounded-5 shadow-sm border border-primary border-opacity-10">
            <div class="text-center mb-4">
                <h4 class="fw-bold">¿Necesitas otro examen?</h4>
                <p class="text-muted">Busca en nuestro catálogo completo de exámenes individuales.</p>
            </div>

            {{-- Aumentamos el max-width a 1200px o lo quitamos para que use el ancho del contenedor --}}
            <div class="mx-auto" style="max-width: 1200px;">
                @livewire('exam-search')
            </div>
        </div>
