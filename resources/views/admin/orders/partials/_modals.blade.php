{{-- Modal de Anulación de Firma (Solo cuando está firmado) --}}
@if($isSigned)
    <div class="modal fade" id="voidModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold">Anular Firma Médica</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.orders.void', ['order' => $order->id]) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning small">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Al anular la firma, el documento actual quedará invalidado y podrá redactar una nueva indicación para esta misma orden.
                        </div>
                        <label class="form-label fw-bold small text-uppercase">Motivo de la anulación:</label>
                        <textarea name="void_reason" class="form-control" rows="4" required
                            placeholder="Ej: Error en el diagnóstico, cambio en los exámenes solicitados, etc."></textarea>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-dark px-4">Confirmar Anulación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if(!$isClosed)
    {{-- Modal de Rechazo --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">Rechazar Requerimiento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.orders.reject', ['order' => $order->id]) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted small mb-3">Indique claramente el motivo por el cual no se puede emitir esta orden. <strong>Esto disparará un reembolso automático al paciente.</strong></p>
                        <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="Motivo del rechazo..."></textarea>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger px-4">Confirmar Rechazo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal de Derivación --}}
    @if(!$order->exam_type_id)
    <div class="modal fade" id="derivateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Derivar Solicitud</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.orders.derivate', ['order' => $order->id]) }}" method="POST">
                    @csrf
                    <div class="modal-body">
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
@endif
