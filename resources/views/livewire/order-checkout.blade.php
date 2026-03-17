<div class="container-fluid px-0"> {{-- Contenedor más amplio --}}
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-9"> {{-- Tarjeta más ancha para aprovechar el espacio --}}

            <div class="p-1">
                {{-- Título y Grid de Perfiles --}}
                <div class="mb-4">
                    <h5 class="fw-bold mb-1 text-dark">¿Para quién es el examen?</h5>
                    <p class="text-muted small">Selecciona un perfil o agrega a un familiar para continuar.</p>
                </div>

                <div class="row g-3 mb-4">
                    @foreach($patients as $p)
                        <div class="col-6 col-sm-4 col-md-3" wire:key="p-{{ $p->id }}"> {{-- Ajustado para más columnas en pantallas grandes --}}
                            <div wire:click="selectPatient({{ $p->id }})"
                                 class="card h-100 border-2 transition-all shadow-sm {{ $selected_patient_id == $p->id ? 'border-primary bg-primary-subtle' : 'border-light bg-white' }}"
                                 style="cursor: pointer; border-radius: 20px;">
                                <div class="card-body p-3 text-center">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($p->full_name) }}&background={{ $selected_patient_id == $p->id ? '0D6EFD' : 'E2E8F0' }}&color={{ $selected_patient_id == $p->id ? 'fff' : '64748B' }}&rounded=true" width="48" class="mb-2">
                                    <p class="mb-0 fw-bold small text-truncate text-dark">{{ $p->full_name }}</p>
                                    <span class="text-muted" style="font-size: 0.65rem;">{{ $p->relationship == 'self' ? 'Tú' : ucfirst($p->relationship) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="col-6 col-sm-4 col-md-3">
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
                    <div class="card border-0 shadow-sm mb-4 animate__animated animate__fadeIn" style="border-radius: 24px; background-color: #f8fafc;">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3"><i class="bi bi-pencil-square me-2 text-primary"></i>Datos del Nuevo Familiar</h6>
                            <div class="row g-3">
                                {{-- Campos del formulario (se mantienen iguales) --}}
                                <div class="col-12">
                                    <label class="small fw-bold text-muted">Nombre Completo</label>
                                    <input type="text" wire:model="new_full_name" class="form-control rounded-3 @error('new_full_name') is-invalid @enderror" placeholder="Ej: Juan Pérez">
                                    @error('new_full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="small fw-bold text-muted">RUT</label>
                                    <input type="text" wire:model.live="new_rut" class="form-control rounded-3 @error('new_rut') is-invalid @enderror" placeholder="12.345.678-k" x-data x-on:input="$el.value = (function(v){ v = v.replace(/[^\dkK]/g,''); if(v.length <= 1) return v.toUpperCase(); let dv = v.slice(-1).toUpperCase(); let body = v.slice(0, -1); return body.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + '-' + dv; })($el.value)">
                                    @error('new_rut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="small fw-bold text-muted">Parentesco</label>
                                    <select wire:model="new_relationship" class="form-select rounded-3 @error('new_relationship') is-invalid @enderror">
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
                                    <input type="date" wire:model="new_birth_date" class="form-control rounded-3 @error('new_birth_date') is-invalid @enderror">
                                    @error('new_birth_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 text-center">
                                    <label class="small fw-bold text-muted d-block">Sexo Biológico</label>
                                    <div class="btn-group w-100">
                                        <input type="radio" class="btn-check" wire:model="new_gender_biologic" value="Masculino" id="sexM">
                                        <label class="btn btn-outline-primary" for="sexM">Masculino</label>
                                        <input type="radio" class="btn-check" wire:model="new_gender_biologic" value="Femenino" id="sexF">
                                        <label class="btn btn-outline-primary" for="sexF">Femenino</label>
                                    </div>
                                    @error('new_gender_biologic') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 mt-4">
                                    <button wire:click="saveFamily" wire:loading.attr="disabled" class="btn btn-primary w-100 fw-bold rounded-pill py-2 shadow-sm">
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
                        <div class="card border-0 shadow-lg mt-4" style="border-radius: 28px; background: white;">
                            <div class="p-4 text-white text-center" style="background: linear-gradient(135deg, #0d6efd 0%, #00d4ff 100%); border-radius: 28px 28px 0 0;">
                                <p class="small text-uppercase fw-bold opacity-75 mb-1">Resumen de Orden</p>
                                <h3 class="mb-0 fw-bold">{{ $exam_type->name }}</h3>
                            </div>
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-6 border-end-md">
                                        <div class="bg-light p-3 rounded-4 mb-3 mb-md-0">
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
                                    </div>
                                    <div class="col-md-6 text-center text-md-end">
                                        <div class="px-2">
                                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Total a pagar</small>
                                            <h2 class="fw-extrabold text-primary mb-0">${{ number_format($exam_type->base_price, 0, ',', '.') }}</h2>
                                        </div>
                                    </div>
                                </div>

                                {{-- AVISO LEGAL DE NO DEVOLUCIÓN --}}
                                <div class="alert alert-warning border-0 mt-4 mb-4 d-flex align-items-start rounded-4 shadow-sm" style="background-color: #fff9eb;">
                                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-warning"></i>
                                    <div>
                                        <strong class="text-dark small d-block mb-1">Aviso importante</strong>
                                        <p class="mb-0 text-muted" style="font-size: 0.8rem; line-height: 1.2;">
                                            Este es un <strong>producto digital de consumo inmediato</strong> (orden de examen). Una vez procesado el pago, el servicio se considera entregado y <strong>no aplica derecho a devolución o retracto</strong>.
                                        </p>
                                    </div>
                                </div>

                                <form action="{{ route('orders.store.public') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="patient_id" value="{{ $selected_patient_id }}">
                                    <input type="hidden" name="exam_type_id" value="{{ $exam_type->id }}">
                                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow-sm rounded-pill transition-all" style="font-size: 1.1rem;">
                                        Aceptar y Pagar <i class="bi bi-credit-card ms-2"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>
</div>

<style>
    .transition-all { transition: all 0.3s ease; }
    .border-end-md { border-right: 1px solid #e2e8f0; }
    @media (max-width: 767.98px) {
        .border-end-md { border-right: none; }
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3) !important;
    }
</style>
