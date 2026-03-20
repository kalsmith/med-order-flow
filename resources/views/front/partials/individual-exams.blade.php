        {{-- 3. EXÁMENES INDIVIDUALES --}}
        <div id="frecuentes" class="mb-5">
            <h3 class="fw-bold mb-4"><i class="bi bi-star-fill text-warning"></i> Exámenes Frecuentes</h3>
            <div class="row g-3">
                @foreach($individuales as $exam)
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card h-100 shadow-sm border-0 p-3 rounded-4">
                        <div class="d-flex flex-column h-100">
                            <h6 class="fw-bold mb-2 text-dark">{{ $exam->name }}</h6>
                            <div class="mt-auto d-flex justify-content-between align-items-center pt-2">
                                <span class="text-primary fw-bold">${{ number_format($exam->base_price, 0, ',', '.') }}</span>
                                <a href="{{ route('order.flow', ['type' => 'exam', 'id' => $exam->id]) }}" class="btn btn-outline-primary btn-sm px-3 rounded-pill">Pedir</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
