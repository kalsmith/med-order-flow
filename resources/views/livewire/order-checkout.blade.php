<div class="w-100"> {{-- Usamos el 100% del ancho disponible --}}

    {{-- Título y Grid de Perfiles --}}
    <div class="mb-4">
        <h5 class="fw-bold mb-1 text-dark">¿Para quién es el examen?</h5>
        <p class="text-muted small">Selecciona un perfil o agrega a un familiar para continuar.</p>
    </div>

    {{-- Grid de Pacientes: 4 columnas en PC, 3 en Tablet, 2 en Móvil --}}
    <div class="row g-3 mb-5">
        @foreach($patients as $p)
            <div class="col-6 col-sm-4 col-md-3" wire:key="p-{{ $p->id }}">
                <div wire:click="selectPatient({{ $p->id }})"
                     class="card h-100 border-2 transition-all shadow-sm {{ $selected_patient_id == $p->id ? 'border-primary bg-primary-subtle' : 'border-light bg-white' }}"
                     style="cursor: pointer; border-radius: 20px;">
                    <div class="card-body p-3 text-center">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($p->full_name) }}&background={{ $selected_patient_id == $p->id ? '0D6EFD' : 'E2E8F0' }}&color={{ $selected_patient_id == $p->id ? 'fff' : '64748B' }}&rounded=true" width="56" class="mb-2 shadow-sm">
                        <p class="mb-0 fw-bold small text-truncate text-dark">{{ $p->full_name }}</p>
                        <span class="text-muted" style="font-size: 0.7rem;">{{ $p->relationship == 'self' ? 'Tú' : ucfirst($p->relationship) }}</span>
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

    {{-- Formulario (Ancho completo) --}}
    @if($showAddFamily)
        <div class="card border-0 shadow-sm mb-5 animate__animated animate__fadeIn" style="border-radius: 24px; background-color: #f8fafc;">
            <div class="card-body p-4 p-md-5">
                <h6 class="fw-bold mb-4 text-primary"><i class="bi bi-pencil-square me-2"></i>Datos del Nuevo Familiar</h6>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="small fw-bold text-muted mb-2">Nombre Completo</label>
                        <input type="text" wire:model="new_full_name" class="form-control form-control-lg rounded-3 @error('new_full_name') is-invalid @enderror" placeholder="Ej: Juan Pérez">
                    </div>
                    <div class="col-md-6">
                        <label class="small fw-bold text-muted mb-2">RUT</label>
                        <input type="text" wire:model.live="new_rut" class="form-control form-control-lg rounded-3 @error('new_rut') is-invalid @enderror" placeholder="12.345.678-k">
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted mb-2">Parentesco</label>
                        <select wire:model="new_relationship" class="form-select form-select-lg rounded-3">
                            <option value="">Seleccionar...</option>
                            <option value="hijo">Hijo/a</option>
                            <option value="conyuge">Cónyuge</option>
                            <option value="padre">Padre/Madre</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted mb-2">Fecha Nacimiento</label>
                        <input type="date" wire:model="new_birth_date" class="form-control form-control-lg rounded-3">
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted mb-2 d-block">Sexo Biológico</label>
                        <div class="btn-group w-100 h-75">
                            <input type="radio" class="btn-check" wire:model="new_gender_biologic" value="Masculino" id="sexM">
                            <label class="btn btn-outline-primary d-flex align-items-center justify-content-center" for="sexM">Masculino</label>
                            <input type="radio" class="btn-check" wire:model="new_gender_biologic" value="Femenino" id="sexF">
                            <label class="btn btn-outline-primary d-flex align-items-center justify-content-center" for="sexF">Femenino</label>
                        </div>
                    </div>
                    <div class="col-12 mt-4">
                        <button wire:click="saveFamily" class="btn btn-primary btn-lg w-100 fw-bold rounded-pill shadow-sm">Guardar y Seleccionar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Resumen Final (Ancho completo) --}}
    @if($selected_patient_id)
        @php $sel = $patients->firstWhere('id', $selected_patient_id); @endphp
        <div class="card border-0 shadow-lg overflow-hidden" style="border-radius: 30px;">
            <div class="row g-0">
                {{-- Lado izquierdo: Info --}}
                <div class="col-md-8 p-4 p-md-5">
                    <div class="d-flex align-items-center mb-4 text-primary">
                        <i class="bi bi-file-earmark-check fs-3 me-2"></i>
                        <h4 class="mb-0 fw-bold">Confirmación de Orden</h4>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Examen seleccionado:</small>
                            <span class="fw-bold fs-5 text-dark">{{ $exam_type->name }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Paciente:</small>
                            <span class="fw-bold fs-5 text-dark">{{ $sel->full_name }}</span>
                        </div>
                    </div>

                    {{-- AVISO DE NO DEVOLUCIÓN --}}
                    <div class="mt-5 p-3 rounded-4" style="background-color: #fff8f0; border: 1px solid #ffe8cc;">
                        <div class="d-flex">
                            <i class="bi bi-info-circle-fill text-warning me-3 fs-4"></i>
                            <div>
                                <p class="mb-0 text-dark small fw-bold">Producto digital de consumo inmediato</p>
                                <p class="mb-0 text-muted" style="font-size: 0.8rem;">
                                    Al ser una orden de examen generada al instante, **no se aceptan devoluciones** una vez realizado el pago.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Lado derecho: Pago --}}
                <div class="col-md-4 bg-light p-4 p-md-5 d-flex flex-column justify-content-center border-start text-center">
                    <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Total a pagar</small>
                    <h1 class="fw-extrabold text-primary my-3">${{ number_format($exam_type->base_price, 0, ',', '.') }}</h1>

                    <form action="{{ route('orders.store.public') }}" method="POST">
                        @csrf
                        <input type="hidden" name="patient_id" value="{{ $selected_patient_id }}">
                        <input type="hidden" name="exam_type_id" value="{{ $exam_type->id }}">
                        <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold py-3 shadow">
                            Ir al Pago <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>

<style>
    .transition-all { transition: all 0.25s ease-in-out; }
    .transition-all:hover { transform: translateY(-5px); }
    .ls-1 { letter-spacing: 1px; }
    .fw-extrabold { font-weight: 800; }
</style>
