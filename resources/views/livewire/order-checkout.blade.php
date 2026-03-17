<div class="p-1">
    {{-- Título y Grid de Perfiles --}}
    <div class="mb-4">
        <h5 class="fw-bold mb-1">¿Para quién es el examen?</h5>
        <p class="text-muted small">Selecciona un perfil o agrega a un familiar.</p>
    </div>

    {{-- Grid de Pacientes: Ahora usamos col-lg-3 para meter 4 por fila en desktop --}}
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

    {{-- Formulario de Familiar (Ajustado para no verse gigante) --}}
    @if($showAddFamily)
        <div class="card border-0 shadow-sm mb-5 animate__animated animate__fadeIn" style="border-radius: 24px; background-color: #f8fafc;">
            <div class="card-body p-4 p-md-5">
                <h6 class="fw-bold mb-4 text-primary"><i class="bi bi-pencil-square me-2"></i>Datos del Nuevo Familiar</h6>
                <div class="row g-4">
                    <div class="col-md-8">
                        <label class="small fw-bold text-muted">Nombre Completo</label>
                        <input type="text" wire:model="new_full_name" class="form-control border-0 shadow-sm py-2" placeholder="Ej: Juan Pérez" style="border-radius: 12px;">
                        @error('new_full_name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted">RUT</label>
                        <input type="text" wire:model.live="new_rut" class="form-control border-0 shadow-sm py-2" placeholder="12.345.678-k" style="border-radius: 12px;">
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted">Parentesco</label>
                        <select wire:model="new_relationship" class="form-select border-0 shadow-sm py-2" style="border-radius: 12px;">
                            <option value="">Seleccionar...</option>
                            <option value="hijo">Hijo/a</option>
                            <option value="conyuge">Cónyuge</option>
                            <option value="padre">Padre/Madre</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted">Fecha Nacimiento</label>
                        <input type="date" wire:model="new_birth_date" class="form-control border-0 shadow-sm py-2" style="border-radius: 12px;">
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted">Sexo Biológico</label>
                        <div class="btn-group w-100">
                            <input type="radio" class="btn-check" wire:model="new_gender_biologic" value="Masculino" id="sexM">
                            <label class="btn btn-outline-primary py-2" for="sexM" style="border-radius: 12px 0 0 12px;">Masc.</label>
                            <input type="radio" class="btn-check" wire:model="new_gender_biologic" value="Femenino" id="sexF">
                            <label class="btn btn-outline-primary py-2" for="sexF" style="border-radius: 0 12px 12px 0;">Fem.</label>
                        </div>
                    </div>
                    <div class="col-12 text-end mt-4">
                        <button wire:click="saveFamily" wire:loading.attr="disabled" class="btn btn-primary px-5 fw-bold rounded-pill py-3 shadow">
                            <span wire:loading.remove>Guardar y Seleccionar</span>
                            <span wire:loading>Procesando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- RESUMEN DE PAGO: DISEÑO HORIZONTAL PARA LLENAR EL ESPACIO --}}
    @if($selected_patient_id)
        @php $sel = $patients->firstWhere('id', $selected_patient_id); @endphp
        @if($sel)
            <div class="card border-0 shadow-lg overflow-hidden" style="border-radius: 35px; background: white;">
                <div class="row g-0">
                    {{-- Bloque Izquierdo: Información --}}
                    <div class="col-md-7 p-4 p-lg-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                <i class="bi bi-cart-check fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold">Confirmación de Orden</h4>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-6">
                                <p class="text-muted small text-uppercase fw-bold mb-1">Examen</p>
                                <p class="fw-bold text-dark fs-5">{{ $exam_type->name }}</p>
                            </div>
                            <div class="col-6">
                                <p class="text-muted small text-uppercase fw-bold mb-1">Paciente</p>
                                <p class="fw-bold text-dark fs-5 text-truncate">{{ $sel->full_name }}</p>
                            </div>
                        </div>

                        {{-- DISCLAIMER LEGAL INTEGRADO --}}
                        <div class="p-3 rounded-4 mt-2" style="background-color: #fff9f0; border: 1px solid #ffe8cc;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle-fill text-warning me-3 fs-4"></i>
                                <p class="mb-0 text-muted small" style="line-height: 1.4;">
                                    Este es un <strong>producto digital de consumo inmediato</strong>. Una vez generado el pago, el servicio se considera entregado y no aplica derecho a retracto.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Bloque Derecho: Precio y Botón --}}
                    <div class="col-md-5 bg-light p-4 p-lg-5 text-center d-flex flex-column justify-content-center border-start">
                        <p class="text-muted text-uppercase fw-bold small mb-2" style="letter-spacing: 1px;">Total Final</p>
                        <h2 class="display-4 fw-bold text-primary mb-4">${{ number_format($exam_type->base_price, 0, ',', '.') }}</h2>

                        <form action="{{ route('orders.store.public') }}" method="POST">
                            @csrf
                            <input type="hidden" name="patient_id" value="{{ $selected_patient_id }}">
                            <input type="hidden" name="exam_type_id" value="{{ $exam_type->id }}">
                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow" style="border-radius: 20px;">
                                Continuar al Pago <i class="bi bi-credit-card ms-2"></i>
                            </button>
                        </form>
                        <p class="text-muted mt-3 mb-0" style="font-size: 0.75rem;"><i class="bi bi-shield-check me-1"></i> Transacción protegida por Webpay</p>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
