<div class="p-1">
    {{-- Título y Grid de Perfiles --}}
    <div class="mb-4">
        <h5 class="fw-bold mb-1">¿Para quién es el examen?</h5>
        <p class="text-muted small">Selecciona un perfil o agrega a un familiar.</p>
    </div>

    {{-- Grid de Pacientes: Expandido a 4 columnas en desktop --}}
    <div class="row g-3 mb-5">
        @foreach($patients as $p)
            <div class="col-6 col-md-4 col-lg-3" wire:key="p-{{ $p->id }}">
                <div wire:click="selectPatient({{ $p->id }})"
                     class="card h-100 border-2 transition-all shadow-sm {{ $selected_patient_id == $p->id ? 'border-primary bg-primary-subtle' : 'border-light bg-white' }}"
                     style="cursor: pointer; border-radius: 20px;">
                    <div class="card-body p-3 text-center">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($p->full_name) }}&background={{ $selected_patient_id == $p->id ? '0D6EFD' : 'E2E8F0' }}&color={{ $selected_patient_id == $p->id ? 'fff' : '64748B' }}&rounded=true" width="48" class="mb-2">
                        <p class="mb-0 fw-bold small text-truncate">{{ $p->full_name }}</p>
                        <span class="text-muted" style="font-size: 0.65rem;">{{ $p->relationship == 'self' ? 'Tú' : ucfirst($p->relationship) }}</span>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="col-6 col-md-4 col-lg-3">
            <div wire:click="toggleAddFamily" class="card h-100 border-2 border-dashed {{ $showAddFamily ? 'border-primary bg-light' : 'border-muted' }} transition-all" style="cursor: pointer; border-radius: 20px; border-style: dashed !important;">
                <div class="card-body p-3 text-center d-flex flex-column justify-content-center align-items-center">
                    <i class="bi bi-person-plus fs-2 text-muted mb-1"></i>
                    <p class="mb-0 fw-bold small text-muted">Añadir familiar</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulario de Familiar --}}
    @if($showAddFamily)
        <div class="card border-0 shadow-sm mb-5 animate__animated animate__fadeIn" style="border-radius: 24px; background-color: #f8fafc;">
            <div class="card-body p-4 p-md-5">
                <h6 class="fw-bold mb-4 text-primary"><i class="bi bi-pencil-square me-2"></i>Datos del Familiar</h6>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="small fw-bold text-muted">Nombre Completo</label>
                        <input type="text" wire:model="new_full_name" class="form-control" placeholder="Ej: Juan Pérez">
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted">RUT</label>
                        <input type="text" wire:model.live="new_rut" class="form-control" placeholder="12.345.678-k">
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted">Parentesco</label>
                        <select wire:model="new_relationship" class="form-select">
                            <option value="">Seleccionar...</option>
                            <option value="hijo">Hijo/a</option>
                            <option value="conyuge">Cónyuge</option>
                            <option value="padre">Padre/Madre</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted">Fecha Nacimiento</label>
                        <input type="date" wire:model="new_birth_date" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted">Sexo Biológico</label>
                        <div class="btn-group w-100">
                            <input type="radio" class="btn-check" wire:model="new_gender_biologic" value="Masculino" id="sexM">
                            <label class="btn btn-outline-primary" for="sexM">Masc.</label>
                            <input type="radio" class="btn-check" wire:model="new_gender_biologic" value="Femenino" id="sexF">
                            <label class="btn btn-outline-primary" for="sexF">Fem.</label>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <button wire:click="saveFamily" wire:loading.attr="disabled" class="btn btn-primary px-5 fw-bold rounded-pill">
                            Guardar y Seleccionar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- RESUMEN DE PAGO: DISEÑO HORIZONTAL EXPANDIDO --}}
    @if($selected_patient_id)
        @php $sel = $patients->firstWhere('id', $selected_patient_id); @endphp
        @if($sel)
            <div class="card border-0 shadow-lg overflow-hidden" style="border-radius: 30px; background: white;">
                <div class="row g-0">
                    {{-- Bloque Izquierdo: Información y Razones Legales --}}
                    <div class="col-md-7 p-4 p-lg-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="bi bi-file-earmark-medical fs-3 text-primary"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">Confirmación de Orden</h4>
                                <p class="text-muted mb-0 small">Verifica los datos del paciente y del examen.</p>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <span class="text-muted small text-uppercase fw-bold d-block">Examen</span>
                                <span class="fw-bold text-dark fs-5">{{ $exam_type->name }}</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small text-uppercase fw-bold d-block">Paciente</span>
                                <span class="fw-bold text-dark fs-5">{{ $sel->full_name }}</span>
                            </div>
                        </div>

                        {{-- SECCIÓN DE POLÍTICA DE DEVOLUCIÓN --}}
                        <div class="p-3 rounded-4" style="background-color: #fef2f2; border: 1px solid #fee2e2;">
                            <div class="d-flex mb-2">
                                <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                                <span class="fw-bold text-danger small">Aviso importante: Sin derecho a retracto</span>
                            </div>
                            <p class="mb-0 text-muted" style="font-size: 0.8rem; line-height: 1.4;">
                                Al tratarse de un <strong>servicio digital de ejecución inmediata</strong> (generación automática de orden médica), el servicio se considera prestado al momento del pago. Por esta razón, no se admiten devoluciones ni anulaciones una vez finalizada la transacción.
                            </p>
                        </div>
                    </div>

                    {{-- Bloque Derecho: Pago --}}
                    <div class="col-md-5 bg-light p-4 p-lg-5 text-center d-flex flex-column justify-content-center border-start">
                        <span class="text-muted fw-bold small text-uppercase mb-2" style="letter-spacing: 1px;">Total a pagar</span>
                        <h2 class="display-4 fw-extrabold text-primary mb-4">${{ number_format($exam_type->base_price, 0, ',', '.') }}</h2>

                        <form action="{{ route('orders.store.public') }}" method="POST">
                            @csrf
                            <input type="hidden" name="patient_id" value="{{ $selected_patient_id }}">
                            <input type="hidden" name="exam_type_id" value="{{ $exam_type->id }}">
                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow" style="border-radius: 18px;">
                                Continuar al Pago <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </form>

                        <div class="mt-4 d-flex justify-content-center gap-3 opacity-50">
                            <i class="bi bi-shield-lock-fill small"> Pago Seguro</i>
                            <i class="bi bi-lightning-charge-fill small"> Entrega Instantánea</i>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

<style>
    .fw-extrabold { font-weight: 800; }
    .transition-all:hover { transform: translateY(-3px); transition: all 0.2s; }
</style>
