        {{-- 2. BANNER ORDEN PERSONALIZADA --}}
        <div class="card bg-dark text-white border-0 shadow-lg p-4 p-md-5 rounded-5 mb-5 overflow-hidden position-relative">
            <div class="row align-items-center position-relative" style="z-index: 2;">
                <div class="col-md-8 mb-4 mb-md-0">
                    <h2 class="fw-bold mb-3">¿No encuentras el examen que buscas?</h2>
                    <p class="lead opacity-75 mb-0">Carga tu lista de exámenes y un médico colegiado emitirá una orden personalizada a tu medida.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="mb-3">
                        <span class="fs-4">Desde</span>
                        <span class="display-6 fw-bold text-primary"> $9.990</span>
                    </div>
                    <a href="{{ route('order.flow', ['type' => 'personalizada']) }}" class="btn btn-primary btn-lg px-5 py-3 fw-bold rounded-pill shadow">Solicitar a Medida</a>
                </div>
            </div>
            <i class="bi bi-clipboard2-pulse position-absolute text-white opacity-10" style="font-size: 10rem; right: -20px; bottom: -30px; transform: rotate(-15deg);"></i>
        </div>
