<div>
    <form wire:submit.prevent="submit">

        {{-- Selector de Pacientes --}}
        <div class="form-section">
            <label class="form-label fw-bold small text-muted text-uppercase mb-3">
                <i class="bi bi-people-fill me-1"></i> ¿Para quién es el examen?
            </label>

            <div class="row g-3 mb-2">
                @foreach($patients as $p)
                    <div class="col-6 col-sm-4">
                        <div wire:click="selectPatient({{ $p->id }})"
                             class="card h-100 border-2 transition-all shadow-sm patient-card {{ $selected_patient_id == $p->id ? 'border-primary bg-primary-subtle' : 'border-light bg-white' }}"
                             style="cursor: pointer;">
                            <div class="card-body p-3 text-center">
                                <div class="mb-2">
                                    <i class="bi bi-person-circle fs-2 {{ $selected_patient_id == $p->id ? 'text-primary' : 'text-muted' }}"></i>
                                </div>
                                <p class="mb-1 fw-bold small text-truncate text-dark">{{ $p->full_name }}</p>
                                <span class="badge rounded-pill {{ $selected_patient_id == $p->id ? 'bg-primary' : 'bg-light text-muted border' }}" style="font-size: 0.65rem;">
                                    {{ $p->relationship == 'self' ? 'Tú' : __($p->relationship) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Botón Agregar Familiar --}}
                <div class="col-6 col-sm-4">
                    <a href="{{ route('profile.complete', ['add_family' => 1]) }}" class="text-decoration-none h-100">
                        <div class="card h-100 border-2 border-dashed border-muted bg-white transition-all patient-card">
                            <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                                <i class="bi bi-person-plus fs-2 text-muted mb-1"></i>
                                <p class="mb-0 fw-bold small text-muted">Otro familiar</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            @error('selected_patient_id') <span class="text-danger tiny">Por favor selecciona un paciente</span> @enderror
        </div>

        {{-- Exámenes --}}
        <div class="form-section">
            <label class="form-label fw-bold small text-muted text-uppercase">Exámenes requeridos</label>
            <textarea wire:model="custom_exam_name"
                class="form-control bg-light border-0 p-3 @error('custom_exam_name') is-invalid @enderror"
                rows="3"
                placeholder="Ej: Hemograma completo, Perfil Lipídico..."></textarea>
            @error('custom_exam_name') <div class="invalid-feedback">Indica qué exámenes necesitas</div> @enderror
        </div>

        {{-- Síntomas --}}
        <div class="form-section">
            <label class="form-label fw-bold small text-muted text-uppercase">Síntomas o Motivo</label>
            <textarea wire:model="symptoms"
                class="form-control bg-light border-0 p-3"
                rows="2"
                placeholder="Ej: Control de rutina, fatiga constante..."></textarea>
        </div>

        {{-- Selectores Inferiores --}}
        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Rango de Edad</label>
                <select wire:model="patient_type" class="form-select bg-light border-0">
                    <option value="adulto">Adulto (18+ años)</option>
                    <option value="pediatrico">Pediátrico</option>
                    <option value="geriatrico">Adulto Mayor</option>
                </select>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Urgencia</label>
                <select wire:model="urgency" class="form-select bg-light border-0">
                    <option value="normal">Preventivo / Rutina</option>
                    <option value="seguimiento">Seguimiento</option>
                    <option value="urgente">Molestias actuales</option>
                </select>
            </div>
        </div>

        {{-- Precio --}}
        <div class="alert bg-light border-0 d-flex align-items-center rounded-4 p-3 mb-4">
            <i class="bi bi-tag-fill me-3 fs-4 text-primary"></i>
            <div>
                <div class="small text-muted">Costo total del servicio</div>
                <div class="fw-bold fs-5">$9.990</div>
            </div>
        </div>

        {{-- Botón de envío con estado de carga --}}
        <button type="submit" class="btn btn-gradient btn-lg w-100 py-3 fw-bold rounded-4 shadow-sm" wire:loading.attr="disabled">
            <span wire:loading.remove>Continuar al Pago <i class="bi bi-arrow-right ms-2"></i></span>
            <span wire:loading>
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Procesando solicitud...
            </span>
        </button>
    </form>
</div>
