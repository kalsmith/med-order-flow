<div class="p-1">
    {{-- Título y Grid de Perfiles --}}
    <div class="mb-4">
        <h5 class="fw-bold mb-1">¿Para quién es el examen?</h5>
        <p class="text-muted small">Selecciona un perfil o agrega a un familiar.</p>
    </div>

    <div class="row g-3 mb-4">
        @foreach($patients as $p)
            <div class="col-6 col-md-4" wire:key="p-{{ $p->id }}">
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

        <div class="col-6 col-md-4">
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
        <div class="card border-0 shadow-sm mb-4 animate__animated animate__fadeIn" style="border-radius: 24px; background-color: #f1f5f9;">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-pencil-square me-2"></i>Datos del Familiar</h6>
                <div class="row g-3">

                    {{-- Nombre Completo --}}
                    <div class="col-12">
                        <label class="small fw-bold text-muted">Nombre Completo</label>
                        <input type="text" wire:model="new_full_name" class="form-control @error('new_full_name') is-invalid @enderror" placeholder="Ej: Juan Pérez">
                        @error('new_full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- RUT --}}
                    <div class="col-md-6">
                        <label class="small fw-bold text-muted">RUT</label>
                        <input type="text"
                               wire:model.live="new_rut"
                               class="form-control @error('new_rut') is-invalid @enderror"
                               placeholder="12.345.678-k"
                               x-data
                               x-on:input="$el.value = (function(v){
                                   v = v.replace(/[^\dkK]/g,'');
                                   if(v.length <= 1) return v.toUpperCase();
                                   let dv = v.slice(-1).toUpperCase();
                                   let body = v.slice(0, -1);
                                   return body.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + '-' + dv;
                               })($el.value)">
                        @error('new_rut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Parentesco --}}
                    <div class="col-md-6">
                        <label class="small fw-bold text-muted">Parentesco</label>
                        <select wire:model="new_relationship" class="form-select @error('new_relationship') is-invalid @enderror">
                            <option value="">Seleccionar...</option>
                            <option value="hijo">Hijo/a</option>
                            <option value="conyuge">Cónyuge</option>
                            <option value="padre">Padre/Madre</option>
                            <option value="otro">Otro</option>
                        </select>
                        @error('new_relationship') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Fecha Nacimiento --}}
                    <div class="col-md-6">
                        <label class="small fw-bold text-muted">Fecha Nacimiento</label>
                        <input type="date" wire:model="new_birth_date" class="form-control @error('new_birth_date') is-invalid @enderror">
                        @error('new_birth_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Sexo Biológico --}}
                    <div class="col-md-6">
                        <label class="small fw-bold text-muted">Sexo Biológico</label>
                        <div class="btn-group w-100">
                            <input type="radio" class="btn-check" wire:model="new_gender_biologic" value="Masculino" id="sexM">
                            <label class="btn btn-outline-primary" for="sexM">Masc.</label>

                            <input type="radio" class="btn-check" wire:model="new_gender_biologic" value="Femenino" id="sexF">
                            <label class="btn btn-outline-primary" for="sexF">Fem.</label>
                        </div>
                        @error('new_gender_biologic') <div class="text-danger small mt-1" style="font-size: 0.875em;">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <button wire:click="saveFamily" wire:loading.attr="disabled" class="btn btn-primary w-100 fw-bold rounded-pill py-2">
                            <span wire:loading.remove>Guardar y Seleccionar</span>
                            <span wire:loading>Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Resumen de Pago --}}
    @if($selected_patient_id)
        @php $sel = $patients->firstWhere('id', $selected_patient_id); @endphp
        @if($sel)
            <div class="card border-0 shadow-lg" style="border-radius: 28px; background: white;">
                <div class="p-4 bg-gradient-blue text-white text-center" style="background: linear-gradient(45deg, #0d6efd, #0099ff); border-radius: 28px 28px 0 0;">
                    <p class="small text-uppercase fw-bold opacity-75 mb-1">Confirmación Final</p>
                    <h4 class="mb-0 fw-bold">{{ $exam_type->name }}</h4>
                </div>
                <div class="card-body p-4">
                    <div class="bg-light p-3 rounded-4 mb-4">
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Paciente:</span>
                            <span class="fw-bold text-dark">{{ $sel->full_name }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">RUT:</span>
                            <span class="fw-bold text-dark">{{ $sel->rut }}</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">Fecha Nac.:</span>
                            <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($sel->birth_date)->format('d/m/Y') }}</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                        <span class="fw-bold text-dark fs-5">Total:</span>
                        <span class="fs-2 fw-extrabold text-primary">${{ number_format($exam_type->base_price, 0, ',', '.') }}</span>
                    </div>

                    <form action="{{ route('orders.store.public') }}" method="POST">
                        @csrf
                        <input type="hidden" name="patient_id" value="{{ $selected_patient_id }}">
                        <input type="hidden" name="exam_type_id" value="{{ $exam_type->id }}">
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow" style="border-radius: 18px;">
                            Continuar al Pago <i class="bi bi-credit-card ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        @endif
    @endif
</div>
