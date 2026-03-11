<div class="animate__animated animate__fadeIn">
    {{-- Selector de Pacientes --}}
    <div class="form-section mb-4">
        <label class="form-label fw-bold small text-muted text-uppercase mb-3">
            <i class="bi bi-people-fill me-1"></i> ¿Para quién es el examen?
        </label>

        <div class="row g-3 mb-3">
            @foreach($patients as $patient)
                <div class="col-6 col-sm-4" wire:key="patient-{{ $patient->id }}">
                    <div wire:click="selectPatient({{ $patient->id }})"
                         class="card h-100 border-2 transition-all shadow-sm patient-card {{ $selected_patient_id == $patient->id ? 'border-primary bg-primary-subtle' : 'border-light bg-white' }}"
                         style="cursor: pointer;">

                        <div class="card-body p-3 text-center">
                            <div class="mb-2">
                                <i class="bi bi-person-circle fs-2 {{ $selected_patient_id == $patient->id ? 'text-primary' : 'text-muted' }}"></i>
                            </div>
                            <p class="mb-1 fw-bold small text-truncate {{ $selected_patient_id == $patient->id ? 'text-primary' : 'text-dark' }}">
                                {{ $patient->full_name }}
                            </p>

                            <span class="badge rounded-pill {{ $selected_patient_id == $patient->id ? 'bg-primary' : 'bg-light text-muted border' }}" style="font-size: 0.65rem;">
                                {{ $patient->relationship == 'self' ? 'Tú' : ucfirst($patient->relationship) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="col-6 col-sm-4">
                <div wire:click="toggleAddFamily"
                     class="card h-100 border-2 border-dashed transition-all patient-card {{ $showAddFamily ? 'border-primary bg-light' : 'border-muted bg-white' }}"
                     style="cursor: pointer;">
                    <div class="card-body p-3 text-center d-flex flex-column justify-content-center align-items-center">
                        <i class="bi bi-person-plus fs-2 {{ $showAddFamily ? 'text-primary' : 'text-muted' }} mb-1"></i>
                        <p class="mb-0 fw-bold small {{ $showAddFamily ? 'text-primary' : 'text-muted' }}">Otro familiar</p>
                    </div>
                </div>
            </div>
        </div>
        @error('selected_patient_id') <span class="text-danger small"><i class="bi bi-exclamation-circle me-1"></i>Debes seleccionar un paciente</span> @enderror
    </div>

    {{-- Formulario Expandible de Nuevo Familiar --}}
    @if($showAddFamily)
        <div class="card border-0 bg-light rounded-4 mb-4 shadow-sm border-start border-primary border-4 animate__animated animate__fadeInUp">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Registrar Nuevo Familiar</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="small fw-bold text-muted">Nombre Completo</label>
                        <input type="text" wire:model="new_full_name" class="form-control border-0 shadow-sm" placeholder="Ej: Juan Pérez">
                        @error('new_full_name') <span class="text-danger tiny">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-6" x-data="{
                        formatRut(value) {
                            if (!value) return '';
                            let rut = value.replace(/[^\dkK]/g, '').toUpperCase();
                            if (rut.length <= 1) return rut;
                            let cuerpo = rut.slice(0, -1);
                            let dv = rut.slice(-1);
                            cuerpo = cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            return cuerpo + '-' + dv;
                        }
                    }">
                        <label class="small fw-bold text-muted">RUT</label>
                        <input type="text"
                               wire:model="new_rut"
                               class="form-control border-0 shadow-sm"
                               placeholder="12.345.678-9"
                               x-on:input="$el.value = formatRut($el.value)"
                               maxlength="12">
                        @error('new_rut') <span class="text-danger tiny">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="small fw-bold text-muted">Fecha de Nacimiento</label>
                        <input type="date" wire:model="new_birth_date" class="form-control border-0 shadow-sm">
                        @error('new_birth_date') <span class="text-danger tiny">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="small fw-bold text-muted">Sexo Biológico</label>
                        <select wire:model="new_gender_biologic" class="form-select border-0 shadow-sm">
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                        </select>
                        @error('new_gender_biologic') <span class="text-danger tiny">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="small fw-bold text-muted">Parentesco</label>
                        <select wire:model="new_relationship" class="form-select border-0 shadow-sm">
                            <option value="">Selecciona...</option>
                            <option value="hijo">Hijo/a</option>
                            <option value="conyuge">Cónyuge / Pareja</option>
                            <option value="padre">Padre / Madre</option>
                            <option value="otro">Otro</option>
                        </select>
                        @error('new_relationship') <span class="text-danger tiny">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <button type="button" wire:click="saveFamily" class="btn btn-primary w-100 fw-bold py-2 rounded-3 shadow-sm">
                            <i class="bi bi-check-lg me-1"></i> Guardar y Seleccionar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Cuerpo Principal de la Orden --}}
    <form wire:submit.prevent="submit" class="{{ $showAddFamily ? 'opacity-50 pointer-events-none' : '' }}">
        <div class="form-section mb-4">
            <label class="form-label fw-bold small text-muted text-uppercase">Exámenes requeridos</label>
            <textarea wire:model="custom_exam_name"
                class="form-control bg-light border-0 p-3 shadow-sm @error('custom_exam_name') is-invalid @enderror"
                rows="3"
                placeholder="Ej: Hemograma completo, Perfil Lipídico..."></textarea>
            @error('custom_exam_name') <div class="invalid-feedback">Indica qué exámenes necesitas</div> @enderror
        </div>

        <div class="form-section mb-4">
            <label class="form-label fw-bold small text-muted text-uppercase">Síntomas o Motivo</label>
            <textarea wire:model="symptoms"
                class="form-control bg-light border-0 p-3 shadow-sm"
                rows="2"
                placeholder="Ej: Control de rutina, fatiga constante..."></textarea>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase">Rango de Edad</label>
                <select wire:model="patient_type" class="form-select bg-light border-0 shadow-sm">
                    <option value="adulto">Adulto (18+ años)</option>
                    <option value="pediatrico">Pediátrico</option>
                    <option value="geriatrico">Adulto Mayor</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase">Urgencia</label>
                <select wire:model="urgency" class="form-select bg-light border-0 shadow-sm">
                    <option value="normal">Preventivo / Rutina</option>
                    <option value="seguimiento">Seguimiento</option>
                    <option value="urgente">Molestias actuales</option>
                </select>
            </div>
        </div>

        <div class="alert bg-primary-subtle border-0 d-flex align-items-center justify-content-between rounded-4 p-3 mb-4 shadow-sm">
            <div class="d-flex align-items-center">
                <i class="bi bi-tag-fill me-3 fs-4 text-primary"></i>
                <div>
                    <div class="small text-muted">Costo total del servicio</div>
                    <div class="fw-bold fs-5 text-primary">$9.990</div>
                </div>
            </div>
            <div class="text-muted small">
                <i class="bi bi-info-circle me-1"></i> Incluye orden médica
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold rounded-4 shadow-sm btn-gradient border-0"
                wire:loading.attr="disabled"
                {{ !$selected_patient_id ? 'disabled' : '' }}>
            <span wire:loading.remove>Continuar al Pago <i class="bi bi-arrow-right ms-2"></i></span>
            <span wire:loading>
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Procesando solicitud...
            </span>
        </button>
    </form>
</div>
