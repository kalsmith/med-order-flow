@extends('layouts.admin')

@section('header', 'Editar Perfil del Médico')

@section('header-actions')
    <a href="{{ route('admin.doctors.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver al listado
    </a>
@endsection

@section('content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">

<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">

        {{-- Alertas de Error --}}
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li><i class="bi bi-exclamation-circle me-2"></i>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm border-0 overflow-hidden">
            {{-- Indicador visual superior (Azul para edición) --}}
            <div class="bg-primary py-1"></div>

            <div class="card-body p-4 p-md-5">
                <form id="doctor-form" action="{{ route('admin.doctors.update', $doctor) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        {{-- Sección: Identidad --}}
                        <div class="col-12">
                            <h6 class="text-primary text-uppercase fw-bold small mb-3">
                                <i class="bi bi-person-badge me-2"></i>Información Personal y de Cuenta
                            </h6>
                            <hr class="mt-0 opacity-10">
                        </div>

                        {{-- Nombre con Selector de Prefijo --}}
                        <div class="col-md-7">
                            <label class="form-label fw-bold small text-muted">Nombre del Profesional</label>
                            <div class="input-group">
                                <select name="prefix" class="form-select bg-light fw-bold border-end-0 @error('prefix') is-invalid @enderror" style="max-width: 90px;">
                                    <option value="Dr." {{ old('prefix', $doctor->prefix) == 'Dr.' ? 'selected' : '' }}>Dr.</option>
                                    <option value="Dra." {{ old('prefix', $doctor->prefix) == 'Dra.' ? 'selected' : '' }}>Dra.</option>
                                </select>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $doctor->user->name) }}" required placeholder="Nombre y Apellidos">
                            </div>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-bold small text-muted">RUT</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-card-text text-muted"></i></span>
                                <input type="text" name="rut" class="form-control border-start-0 ps-0 @error('rut') is-invalid @enderror"
                                       value="{{ old('rut', $doctor->rut) }}">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror"
                                       value="{{ old('email', $doctor->user->email) }}" required>
                            </div>
                        </div>

                        {{-- SECCIÓN: Seguridad --}}
                        <div class="col-12 mt-5">
                            <h6 class="text-primary text-uppercase fw-bold small mb-3">
                                <i class="bi bi-shield-lock me-2"></i>Seguridad y Acceso
                            </h6>
                            <hr class="mt-0 opacity-10">
                            <div class="alert alert-light border small text-muted">
                                <i class="bi bi-info-circle me-1"></i> Deje los campos de contraseña en blanco si no desea realizar cambios.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Nueva Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
                                <input type="password" name="password" class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror"
                                       placeholder="Mínimo 8 caracteres">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Confirmar Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-key-fill text-muted"></i></span>
                                <input type="password" name="password_confirmation" class="form-control border-start-0 ps-0"
                                       placeholder="Repita la contraseña">
                            </div>
                        </div>

                        {{-- Sección: Información Profesional --}}
                        <div class="col-12 mt-5">
                            <h6 class="text-primary text-uppercase fw-bold small mb-3">
                                <i class="bi bi-briefcase me-2"></i>Información Profesional
                            </h6>
                            <hr class="mt-0 opacity-10">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">N° Registro SIS (RNPI)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-hash text-muted"></i></span>
                                <input type="text" name="rnpi_number" class="form-control border-start-0 ps-0 @error('rnpi_number') is-invalid @enderror"
                                       value="{{ old('rnpi_number', $doctor->rnpi_number) }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Estado del Médico</label>
                            <select name="is_active" id="is_active_select" class="form-select border-primary-subtle fw-bold">
                                <option value="1" {{ old('is_active', $doctor->is_active) == 1 ? 'selected' : '' }}>🟢 Activo / Vigente</option>
                                <option value="0" {{ old('is_active', $doctor->is_active) == 0 ? 'selected' : '' }}>🔴 Inactivo</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">Especialidades Asignadas</label>
                            <div class="p-3 border rounded bg-light">
                                <div class="row">
                                    @foreach($specialties as $specialty)
                                        <div class="col-md-4 col-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="specialties[]"
                                                       value="{{ $specialty->id }}" id="spec_{{ $specialty->id }}"
                                                       {{ (is_array(old('specialties')) && in_array($specialty->id, old('specialties'))) || $doctor->specialties->contains($specialty->id) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="spec_{{ $specialty->id }}">
                                                    {{ $specialty->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('specialties') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Sección: Firma Digital --}}
                        <div class="col-md-12 mt-4">
                            <label class="form-label fw-bold small text-muted">Firma Digital (Sello)</label>
                            <div class="d-flex flex-column flex-md-row align-items-center gap-4 p-3 border rounded">
                                <div class="text-center bg-white p-2 border rounded" style="min-width: 150px;">
                                    <img id="signature-preview"
                                         src="{{ $doctor->signature_path ? asset('storage/' . $doctor->signature_path) : 'https://via.placeholder.com/150x80?text=Sin+Firma' }}"
                                         class="img-fluid" style="max-height: 80px; object-fit: contain;">
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="signature" id="signature-input" class="form-control form-control-sm" accept="image/png, image/jpeg">
                                    <small class="text-muted" style="font-size: 0.7rem;">Suba un archivo nuevo para reemplazar el actual.</small>
                                </div>
                            </div>
                        </div>

                        {{-- Botones de Acción --}}
                        <div class="col-12 mt-5 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <button type="button" class="btn btn-link text-danger text-decoration-none small p-0" onclick="confirmDeactivation()">
                                    <i class="bi bi-slash-circle me-1"></i> Desactivar Médico
                                </button>
                                <button type="submit" class="btn btn-primary px-5 shadow fw-bold">
                                    <i class="bi bi-save me-2"></i> Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    let cropper;
    const input = document.getElementById('signature-input');
    const image = document.getElementById('image-to-crop');
    const preview = document.getElementById('signature-preview');
    const modal = new bootstrap.Modal(document.getElementById('cropperModal'));

    // Crear un input hidden para enviar la imagen recortada
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'signature_cropped';
    document.getElementById('doctor-form').appendChild(hiddenInput);

    input.onchange = function (e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            const reader = new FileReader();
            reader.onload = function (event) {
                image.src = event.target.result;
                modal.show();
            };
            reader.readAsDataURL(files[0]);
        }
    };

    document.getElementById('cropperModal').addEventListener('shown.bs.modal', function () {
        cropper = new Cropper(image, {
            aspectRatio: 2 / 1, // Proporción estándar para firmas
            viewMode: 1,
            autoCropArea: 1,
        });
    }).addEventListener('hidden.bs.modal', function () {
        cropper.destroy();
        cropper = null;
    });

    document.getElementById('crop-button').addEventListener('click', function () {
        // Definimos el tamaño estándar de salida (ej: 400x200)
        const canvas = cropper.getCroppedCanvas({
            width: 400,
            height: 200,
        });

        const croppedImageDataURL = canvas.toDataURL('image/png');
        preview.src = croppedImageDataURL; // Actualiza la vista previa en el form
        hiddenInput.value = croppedImageDataURL; // Guarda el Base64 para el servidor
        modal.hide();
    });

    function confirmDeactivation() {
        if (confirm('¿Está seguro de que desea cambiar el estado del médico a Inactivo?')) {
            document.getElementById('is_active_select').value = "0";
            alert('Estado cambiado a Inactivo. Recuerde presionar "Guardar Cambios" para aplicar.');
        }
    }
</script>


<div class="modal fade" id="cropperModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajustar Firma</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="img-container">
                    <img id="image-to-crop" src="" style="max-width: 100%;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="crop-button">Cortar y Usar</button>
            </div>
        </div>
    </div>
</div>


@endsection
