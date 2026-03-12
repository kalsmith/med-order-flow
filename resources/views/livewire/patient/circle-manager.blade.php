<div>
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="fw-extrabold text-dark mb-1" style="letter-spacing: -1px;">Mi Círculo</h1>
            <p class="text-muted mb-0">Gestiona tus datos y los de tus familiares.</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary rounded-4 px-4 py-2 fw-bold shadow-sm">
            <i class="bi bi-plus-lg me-2"></i> Agregar Familiar
        </button>
    </div>

    <div class="card card-circle shadow-sm bg-white">
        <div class="card-body p-4 text-center @if($members->isEmpty()) py-5 @endif">
            @forelse($members as $member)
                <div class="member-row p-3 d-flex align-items-center border border-light" wire:key="member-{{ $member->id }}">
                    <div class="avatar-circle me-3">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="flex-grow-1 text-start">
                        <div class="d-flex align-items-center">
                            <h6 class="fw-bold mb-0 me-2">{{ $member->full_name }}</h6>
                            @if($member->is_primary)
                                <span class="badge bg-primary-subtle text-primary border-primary-subtle px-2" style="font-size: 0.6rem;">TITULAR</span>
                            @endif
                        </div>
                        <small class="text-muted">{{ $member->rut }} • {{ $member->is_primary ? 'Tu perfil' : ucfirst($member->relationship) }}</small>
                    </div>
                    <div class="ms-auto">
                        @if(!$member->is_primary)
                            <button wire:click="confirmDeletion({{ $member->id }})" class="btn btn-light btn-sm rounded-3 text-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-muted my-3">No tienes familiares registrados aún.</p>
            @endforelse
        </div>
    </div>

    @if($showAddModal)
    <div class="modal-backdrop-custom"></div>
    <div class="custom-modal p-3">
        <div class="card border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Nuevo Familiar</h5>
                <button type="button" wire:click="closeModal" class="btn-close"></button>
            </div>
            <div class="card-body p-4">
                <form wire:submit.prevent="save">
                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1">Nombre Completo</label>
                        <input type="text" wire:model="full_name" class="form-control rounded-3 @error('full_name') is-invalid @enderror" placeholder="Ej: Juan Pérez">
                        @error('full_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold text-muted mb-1">RUT</label>
                            <input type="text"
                                   wire:model.live="rut"
                                   class="form-control rounded-3 @error('rut') is-invalid @enderror"
                                   placeholder="12.345.678-k"
                                   x-data
                                   x-on:input="$el.value = (function(v){ v=v.replace(/[^\dkK]/g,''); if(v.length<=1) return v.toUpperCase(); let dv=v.slice(-1).toUpperCase(); let body=v.slice(0,-1); return body.replace(/\B(?=(\d{3})+(?!\d))/g,'.')+'-'+dv; })($el.value)">
                            @error('rut') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold text-muted mb-1">Parentesco</label>
                            <select wire:model="relationship" class="form-select rounded-3 @error('relationship') is-invalid @enderror">
                                <option value="">Seleccionar...</option>
                                <option value="hijo">Hijo/a</option>
                                <option value="conyuge">Cónyuge</option>
                                <option value="padre">Padre/Madre</option>
                                <option value="otro">Otro</option>
                            </select>
                            @error('relationship') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold text-muted mb-1">Fecha Nacimiento</label>
                            <input type="date" wire:model="birth_date" class="form-control rounded-3 @error('birth_date') is-invalid @enderror">
                            @error('birth_date') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold text-muted mb-1">Género Biológico</label>
                            <select wire:model="gender_biologic" class="form-select rounded-3">
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-bold shadow-sm">
                            <span wire:loading.remove>Guardar Familiar</span>
                            <span wire:loading class="spinner-border spinner-border-sm"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if($showDeleteModal)
    <div class="modal-backdrop-custom"></div>
    <div class="custom-modal p-3">
        <div class="card border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="card-body p-5 text-center">
                <div class="mb-4">
                    <i class="bi bi-exclamation-circle text-danger" style="font-size: 3rem;"></i>
                </div>
                <h4 class="fw-extrabold text-dark mb-2">¿Estás seguro?</h4>
                <p class="text-muted">Estás a punto de eliminar a este familiar de tu círculo. Esta acción no se puede deshacer.</p>

                <div class="d-flex gap-2 mt-4">
                    <button wire:click="$set('showDeleteModal', false)" class="btn btn-light w-100 rounded-3 py-2 fw-bold">
                        Cancelar
                    </button>
                    <button wire:click="deleteMember" class="btn btn-danger w-100 rounded-3 py-2 fw-bold">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
