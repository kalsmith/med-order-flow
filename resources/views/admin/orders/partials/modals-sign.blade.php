{{-- MODAL DE RECHAZO --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold">Rechazar Requerimiento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.orders.reject', ['medical_order' => $order->id]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">Explique por qué rechaza esta orden (se enviará al paciente).</p>
                    <textarea name="rejection_reason" class="form-control" rows="4" placeholder="Motivo del rechazo..." required></textarea>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger px-4">Confirmar Rechazo</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL DE DERIVACIÓN (Solo para Custom) --}}
@if(!$order->exam_type_id)
<div class="modal fade" id="derivateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Derivar Solicitud</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.orders.derivate', ['medical_order' => $order->id]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="small text-muted">Si esta solicitud personalizada no corresponde a tu área, selecciona la especialidad correcta.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Asignar a Área:</label>
                        <select name="specialty_id" class="form-select" required>
                            <option value="">-- Seleccionar área --</option>
                            @foreach(\App\Models\Specialty::all() as $spec)
                                <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">Confirmar Derivación</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
