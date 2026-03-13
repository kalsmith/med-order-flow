<div>
    {{-- Header del componente --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-800 text-dark mb-0" style="letter-spacing: -1.5px;">Mi Círculo</h2>
            <p class="text-muted mb-0 small">Gestiona tus datos y los de tus familiares.</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-2"></i> Agregar Familiar
        </button>
    </div>

    {{-- Lista de Miembros --}}
    <div class="card card-custom shadow-sm">
        <div class="card-body p-4">
            @forelse($members as $member)
                <div class="member-row p-3 d-flex align-items-center mb-2" wire:key="member-{{ $member->id }}">
                    <div class="avatar-circle me-3">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="fw-bold mb-0">{{ $member->full_name }}</h6>
                            @if($member->is_primary)
                                <span class="badge badge-status-signed px-2" style="font-size: 0.6rem;">TITULAR</span>
                            @endif
                        </div>
                        <small class="text-muted">{{ $member->rut }} • {{ $member->is_primary ? 'Tu perfil' : ucfirst($member->relationship) }}</small>
                    </div>
                    <div class="ms-auto">
                        @if(!$member->is_primary)
                            <button wire:click="confirmDeletion({{ $member->id }})" class="btn btn-light btn-sm rounded-3 text-danger border-0">
                                <i class="bi bi-trash"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-people text-muted opacity-25" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">No tienes familiares registrados aún.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- MODAL PARA AGREGAR --}}
    @if($showAddModal)
    <div class="modal-backdrop-custom" wire:click="closeModal"></div>
    <div class="custom-modal p-3">
        <div class="card border-0 rounded-4 shadow-lg">
            <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-800 mb-0" style="letter-spacing: -1px;">Nuevo Familiar</h5>
                <button type="button" wire:click="closeModal" class="btn-close"></button>
            </div>
            <div class="card-body p-4">
                <form wire:submit.prevent="save">
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" wire:model="full_name" class="form-control @error('full_name') is-invalid @enderror" placeholder="Ej: Juan Pérez">
                        @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">RUT</label>
                            <input type="text" wire:model.live="rut" class="form-control @error('rut') is-invalid @enderror" placeholder="12.345.678-k"
                                   x-data x-on:input="$el.value = (function(v){ v=v.replace(/[^\dkK]/g,''); if(v.length<=1) return v.toUpperCase(); let dv=v.slice(-1).toUpperCase(); let body=v.slice(0,-1); return body.replace(/\B(?=(\d{3})+(?!\d))/g,'.')+'-'+dv; })($el.value)">
                            @error('rut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parentesco</label>
                            <select wire:model="relationship" class="form-select @error('relationship') is-invalid @enderror">
                                <option value="">Seleccionar...</option>
                                <option value="hijo">Hijo/a</option>
                                <option value="conyuge">Cónyuge</option>
                                <option value="padre">Padre/Madre</option>
                                <option value="otro">Otro</option>
                            </select>
                            @error('relationship') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Nacimiento</label>
                            <input type="date" wire:model="birth_date" class="form-control @error('birth_date') is-invalid @enderror">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Género Biológico</label>
                            <select wire:model="gender_biologic" class="form-select">
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-4 shadow-sm">
                        <span wire:loading.remove>Guardar Familiar</span>
                        <span wire:loading class="spinner-border spinner-border-sm"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL PARA ELIMINAR --}}
    @if($showDeleteModal)
    <div class="modal-backdrop-custom" wire:click="$set('showDeleteModal', false)"></div>
    <div class="custom-modal p-3">
        <div class="card border-0 rounded-4 shadow-lg text-center">
            <div class="card-body p-5">
                <i class="bi bi-exclamation-circle text-danger mb-4 d-block" style="font-size: 3.5rem; opacity: 0.5;"></i>
                <h4 class="fw-800 mb-2">¿Estás seguro?</h4>
                <p class="text-muted mb-4">Esta acción eliminará permanentemente al familiar de tu círculo.</p>
                <div class="d-flex gap-2">
                    <button wire:click="$set('showDeleteModal', false)" class="btn btn-light w-100 fw-bold py-2">Cancelar</button>
                    <button wire:click="deleteMember" class="btn btn-danger w-100 fw-bold py-2 shadow-sm">Eliminar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
