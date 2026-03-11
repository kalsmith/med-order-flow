<div>
    {{-- Selector de Paciente --}}
    <div class="mb-4 text-start">
        <h6 class="fw-bold"><i class="bi bi-people me-2 text-primary"></i>¿Para quién es la orden?</h6>
        <div class="row g-3 mt-1">
            @foreach($patients as $p)
                <div class="col-6 col-md-4">
                    <div wire:click="selectPatient({{ $p->id }})"
                         class="patient-card p-3 text-center transition-all h-100 {{ $selected_patient_id == $p->id ? 'active' : '' }}">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($p->full_name) }}&background={{ $selected_patient_id == $p->id ? '0D6EFD' : 'E2E8F0' }}&color={{ $selected_patient_id == $p->id ? 'fff' : '64748B' }}&rounded=true" width="40" class="mb-2">
                        <p class="small fw-bold mb-0 text-truncate">{{ $p->full_name }}</p>
                    </div>
                </div>
            @endforeach
            <div class="col-6 col-md-4">
                <div wire:click="toggleAddFamily" class="patient-card p-3 text-center h-100 border-dashed {{ $showAddFamily ? 'active' : '' }}">
                    <i class="bi bi-person-plus fs-3 text-muted"></i>
                    <p class="small fw-bold mb-0">Agregar</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulario Familiar (Oculto/Visible) --}}
    @if($showAddFamily)
        <div class="card bg-light border-0 rounded-4 mb-4 animate__animated animate__fadeIn">
            <div class="card-body p-4 text-start">
                <h6 class="fw-bold mb-3 small text-uppercase">Datos del Familiar</h6>
                <div class="row g-3">
                    <div class="col-12">
                        <input type="text" wire:model="new_full_name" class="form-control" placeholder="Nombre Completo">
                    </div>
                    <div class="col-md-6">
                        <input type="text" wire:model.live="new_rut" class="form-control" placeholder="RUT (12.345.678-k)"
                               x-data x-on:input="$el.value = (function(v){ v=v.replace(/[^\dkK]/g,''); if(v.length<=1) return v.toUpperCase(); let dv=v.slice(-1).toUpperCase(); let body=v.slice(0,-1); return body.replace(/\B(?=(\d{3})+(?!\d))/g,'.')+'-'+dv; })($el.value)">
                    </div>
                    <div class="col-md-6">
                        <select wire:model="new_relationship" class="form-select">
                            <option value="">Parentesco...</option>
                            <option value="hijo">Hijo/a</option>
                            <option value="conyuge">Cónyuge</option>
                            <option value="padre">Padre/Madre</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button wire:click="saveFamily" class="btn btn-primary w-100 fw-bold rounded-pill">Guardar Familiar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Formulario de Orden Custom --}}
    @if($selected_patient_id)
        <div class="text-start mt-4 animate__animated animate__fadeIn">
            <label class="fw-bold mb-2 small text-uppercase text-muted">Detalle de tu requerimiento</label>
            <textarea wire:model="description" class="form-control mb-3" rows="5"
                      placeholder="Escribe aquí los exámenes que necesitas o describe tus síntomas para que un médico te oriente..."></textarea>

            <button wire:click="submitRequest" class="btn btn-primary btn-send w-100 shadow">
                Enviar Solicitud <i class="bi bi-send ms-2"></i>
            </button>
        </div>
    @endif
</div>
