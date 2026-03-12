<div>
    {{-- Selector de Paciente --}}
    <div class="mb-4 text-start">
        <h6 class="fw-bold mb-3">
            <i class="bi bi-people me-2 text-primary"></i>¿Para quién es la orden?
        </h6>
        <div class="row g-3">
            @foreach($patients as $p)
                <div class="col-6 col-md-4" wire:key="p-{{ $p->id }}">
                    <div wire:click="selectPatient({{ $p->id }})"
                         class="patient-card p-3 text-center transition-all h-100 {{ $selected_patient_id == $p->id ? 'active shadow-sm border-primary' : '' }}"
                         style="cursor: pointer; border: 2px solid transparent; border-radius: 15px; background: #fff;">

                        <img src="https://ui-avatars.com/api/?name={{ urlencode($p->full_name) }}&background={{ $selected_patient_id == $p->id ? '0D6EFD' : 'E2E8F0' }}&color={{ $selected_patient_id == $p->id ? 'fff' : '64748B' }}&rounded=true"
                             width="40" class="mb-2 shadow-sm">

                        <p class="small fw-bold mb-0 text-truncate">{{ $p->full_name }}</p>
                        <span class="text-muted d-block" style="font-size: 0.7rem;">
                            {{ $p->relationship == 'self' ? 'Tú' : ucfirst($p->relationship) }}
                        </span>
                    </div>
                </div>
            @endforeach

            <div class="col-6 col-md-4">
                <div wire:click="toggleAddFamily"
                     class="patient-card p-3 text-center h-100 border-dashed {{ $showAddFamily ? 'active border-primary' : '' }}"
                     style="cursor: pointer; border: 2px dashed #cbd5e1; border-radius: 15px; background: #f8fafc;">
                    <i class="bi bi-person-plus fs-3 text-muted mb-1 d-block"></i>
                    <p class="small fw-bold mb-0 text-muted">Añadir</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulario Familiar --}}
    @if($showAddFamily)
        <div class="card border-0 rounded-4 mb-4 animate__animated animate__fadeIn" style="background-color: #f1f5f9;">
            <div class="card-body p-4 text-start">
                <h6 class="fw-bold mb-3 small text-uppercase text-primary">Datos del Nuevo Familiar</h6>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="small fw-bold text-muted">Nombre Completo</label>
                        <input type="text" wire:model="new_full_name" class="form-control @error('new_full_name') is-invalid @enderror" placeholder="Ej: Juan Pérez">
                        @error('new_full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="small fw-bold text-muted">RUT</label>
                        <input type="text" wire:model.live="new_rut" class="form-control @error('new_rut') is-invalid @enderror" placeholder="12.345.678-k"
                               x-data x-on:input="$el.value = (function(v){ v=v.replace(/[^\dkK]/g,''); if(v.length<=1) return v.toUpperCase(); let dv=v.slice(-1).toUpperCase(); let body=v.slice(0,-1); return body.replace(/\B(?=(\d{3})+(?!\d))/g,'.')+'-'+dv; })($el.value)">
                        @error('new_rut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

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

                    <div class="col-md-6">
                        <label class="small fw-bold text-muted">Fecha Nacimiento</label>
                        <input type="date" wire:model="new_birth_date" class="form-control @error('new_birth_date') is-invalid @enderror">
                        @error('new_birth_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="small fw-bold text-muted">Sexo Biológico</label>
                        <div class="btn-group w-100">
                            <input type="radio" class="btn-check" wire:model="new_gender_biologic" value="Masculino" id="sexM" autocomplete="off">
                            <label class="btn btn-outline-primary" for="sexM">Masc.</label>

                            <input type="radio" class="btn-check" wire:model="new_gender_biologic" value="Femenino" id="sexF" autocomplete="off">
                            <label class="btn btn-outline-primary" for="sexF">Fem.</label>
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <button wire:click="saveFamily" wire:loading.attr="disabled" class="btn btn-primary w-100 fw-bold rounded-pill py-2">
                            <span wire:loading.remove>Guardar y Seleccionar</span>
                            <span wire:loading>
                                <span class="spinner-border spinner-border-sm me-2"></span>Guardando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

{{-- ... Todo el selector de pacientes y formulario familiar igual ... --}}

@if($selected_patient_id && !$showAddFamily)
    <div class="text-start mt-4 animate__animated animate__fadeIn">
        {{-- Label y TextArea --}}
        <div class="d-flex align-items-center mb-2">
            <label class="fw-bold small text-uppercase text-muted mb-0">Detalle de tu requerimiento</label>
            <hr class="flex-grow-1 ms-3 opacity-10">
        </div>

        <textarea wire:model="description"
                  class="form-control mb-3 @error('description') is-invalid @enderror"
                  rows="5"
                  style="border-radius: 15px; resize: none;"
                  placeholder="Escribe aquí los exámenes que necesitas..."></textarea>
        @error('description') <div class="invalid-feedback mb-3">{{ $message }}</div> @enderror

        {{-- BOTÓN DE ENVÍO --}}
        <button wire:click="submitRequest"
                wire:loading.attr="disabled"
                class="btn btn-primary btn-send w-100 shadow-sm py-3 fw-bold rounded-pill">
            <span wire:loading.remove>Continuar al Pago <i class="bi bi-credit-card ms-2"></i></span>
            <span wire:loading>
                <span class="spinner-border spinner-border-sm me-2"></span>Procesando...
            </span>
        </button>

        {{-- FORMULARIO OCULTO --}}
        {{-- Usamos wire:model para que el valor sea reactivo y no se quede vacío --}}
        <form id="redirect-form" action="{{ route('orders.store.public') }}" method="POST" style="display: none;">
            @csrf
            <input type="hidden" name="patient_id" value="{{ $selected_patient_id }}">
            <input type="hidden" name="type" value="custom">
            {{-- Importante: Usar el valor actual de la propiedad --}}
            <textarea name="custom_description">{{ $description }}</textarea>
        </form>
    </div>
@endif

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('trigger-submit', () => {
            // Un pequeño retraso para asegurar que Livewire terminó de sincronizar el último caracter
            setTimeout(() => {
                document.getElementById('redirect-form').submit();
            }, 150);
        });
    });
</script>
