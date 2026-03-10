<div>
    {{-- Selector de Pacientes Estilo "Perfiles" --}}
    <h6 class="text-muted fw-bold small text-uppercase mb-3" style="font-size: 0.7rem; letter-spacing: 0.5px;">
        <i class="bi bi-people-fill me-1"></i> ¿Para quién es este examen?
    </h6>

    <div class="row g-3 mb-4">
        @foreach($patients as $p)
            <div class="col-6 col-sm-4">
                <div wire:click="selectPatient({{ $p->id }})"
                     class="card h-100 border-2 transition-all shadow-sm {{ $selectedPatientId == $p->id ? 'border-primary bg-primary-subtle' : 'border-light bg-white' }}"
                     style="cursor: pointer; border-radius: 20px;">
                    <div class="card-body p-3 text-center">
                        <div class="mb-2">
                            <i class="bi bi-person-circle fs-2 {{ $selectedPatientId == $p->id ? 'text-primary' : 'text-muted' }}"></i>
                        </div>
                        <p class="mb-1 fw-bold small text-truncate text-dark">{{ $p->full_name }}</p>
                        <span class="badge rounded-pill {{ $selectedPatientId == $p->id ? 'bg-primary' : 'bg-light text-muted border' }}" style="font-size: 0.65rem;">
                            {{ $p->relationship == 'self' ? 'Tú' : __($p->relationship) }}
                        </span>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Botón Agregar Familiar --}}
        <div class="col-6 col-sm-4">
            <div wire:click="toggleNewPatient"
                 class="card h-100 border-2 border-dashed {{ $showNewPatientForm ? 'border-primary bg-light' : 'border-muted' }} transition-all"
                 style="cursor: pointer; border-radius: 20px; border-style: dashed !important;">
                <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                    <i class="bi bi-person-plus fs-2 text-muted mb-1"></i>
                    <p class="mb-0 fw-bold small text-muted">Otro familiar</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulario Inline Nuevo Paciente --}}
    @if($showNewPatientForm)
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; background-color: #f8fafc;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Datos del Familiar</h6>
                    <button type="button" wire:click="toggleNewPatient" class="btn-close small" style="font-size: 0.7rem;"></button>
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="small fw-bold text-muted">Nombre Completo</label>
                        <input type="text" wire:model="newName" class="form-control form-control-lg border-0 shadow-sm @error('newName') is-invalid @enderror" placeholder="Ej: Juan Pérez" style="border-radius: 12px;">
                    </div>
                    <div class="col-md-6">
                        <label class="small fw-bold text-muted">RUT</label>
                        <input type="text" wire:model="newRut" class="form-control form-control-lg border-0 shadow-sm @error('newRut') is-invalid @enderror" placeholder="12.345.678-k" style="border-radius: 12px;">
                    </div>
                    <div class="col-md-6">
                        <label class="small fw-bold text-muted">Parentesco</label>
                        <select wire:model="newRelationship" class="form-select form-select-lg border-0 shadow-sm @error('newRelationship') is-invalid @enderror" style="border-radius: 12px;">
                            <option value="">Seleccionar...</option>
                            <option value="child">Hijo/a</option>
                            <option value="spouse">Pareja / Cónyuge</option>
                            <option value="parent">Padre / Madre</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="small fw-bold text-muted">Fecha Nacimiento</label>
                        <input type="date" wire:model="newBirthDate" class="form-control form-control-lg border-0 shadow-sm @error('newBirthDate') is-invalid @enderror" style="border-radius: 12px;">
                    </div>
                    <div class="col-md-6">
                        <label class="small fw-bold text-muted">Género Biológico</label>
                        <select wire:model="newGender" class="form-select form-select-lg border-0 shadow-sm @error('newGender') is-invalid @enderror" style="border-radius: 12px;">
                            <option value="">Seleccionar...</option>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                        </select>
                    </div>
                    <div class="col-12 mt-4">
                        <button wire:click="saveNewPatient" wire:loading.attr="disabled" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm" style="border-radius: 15px;">
                            <span wire:loading.remove><i class="bi bi-check-lg me-1"></i> Guardar y Seleccionar</span>
                            <span wire:loading><span class="spinner-border spinner-border-sm me-2"></span> Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Resumen Dinámico y Botón de Pago --}}
    @if($selectedPatient)
        <div class="card card-confirm shadow-lg border-0" style="border-radius: 24px; background: white;">
             <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle badge-step text-uppercase px-3 py-2 rounded-pill mb-2">Paso Final</span>
                    <h2 class="fw-bold h3">Resumen de tu Orden</h2>
                </div>

                <div class="bg-light p-3 rounded-4 mb-4 border-start border-primary border-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-primary fw-bold small text-uppercase mb-1" style="font-size: 0.65rem;">Paciente Seleccionado</h6>
                            <p class="mb-0 fw-bold text-dark fs-5">{{ $selectedPatient->full_name }}</p>
                            <p class="mb-0 text-muted small">RUT: {{ $selectedPatient->rut }}</p>
                        </div>
                        <i class="bi bi-person-check-fill text-primary fs-3"></i>
                    </div>
                </div>

                <h6 class="text-muted fw-bold small text-uppercase mb-3" style="font-size: 0.7rem;">Detalle del Examen</h6>

                <div class="summary-item d-flex justify-content-between align-items-center">
                    <div class="pe-3">
                        <span class="d-block fw-semibold text-dark">{{ $exam->name }}</span>
                        @if($exam->description)
                            <span class="text-muted small d-block mt-1">
                                <i class="bi bi-info-circle me-1"></i> {{ $exam->description }}
                            </span>
                        @endif
                    </div>
                    <span class="fw-bold text-dark text-nowrap">${{ number_format($exam->base_price, 0, ',', '.') }}</span>
                </div>

                <div class="summary-item d-flex justify-content-between mt-3 bg-primary-subtle p-3 rounded-4">
                    <span class="fw-bold text-primary fs-5">Total a Pagar</span>
                    <span class="fw-bold text-primary fs-5">${{ number_format($exam->base_price, 0, ',', '.') }}</span>
                </div>

                {{-- Aviso No Reembolso --}}
                <div class="alert-no-refund mt-4 d-flex align-items-start shadow-sm">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-3 fs-4"></i>
                    <div class="small text-dark">
                        <strong class="d-block mb-1">Aviso de Producto Digital</strong>
                        Este es un servicio de emisión inmediata. Una vez realizado el pago, no se aceptan devoluciones.
                    </div>
                </div>

                <form action="{{ route('orders.store.public') }}" method="POST" class="mt-4">
                    @csrf
                    <input type="hidden" name="patient_id" value="{{ $selectedPatientId }}">
                    <input type="hidden" name="exam_type_id" value="{{ $exam->id }}">

                    <div class="form-check mb-4 bg-light p-3 rounded-3 border">
                        <input class="form-check-input ms-0 me-2" type="checkbox" id="terms" required>
                        <label class="form-check-label small text-muted lh-sm" for="terms">
                            Confirmo que los datos de <strong>{{ $selectedPatient->full_name }}</strong> son correctos y acepto los términos.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm btn-checkout" style="border-radius: 18px; padding: 15px;">
                        <i class="bi bi-credit-card-2-back me-2"></i> Pagar con Flow
                    </button>
                </form>
             </div>
        </div>
    @endif
</div>
