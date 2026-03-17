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
            <div class="bg-primary py-1"></div>
            <div class="card-body p-4 p-md-5">
                <form id="doctor-form" action="{{ route('admin.doctors.update', $doctor) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        {{-- ... Tus campos anteriores (Nombre, RUT, Email, etc.) se mantienen igual ... --}}
                        <div class="col-md-7">
                            <label class="form-label fw-bold small text-muted">Nombre del Profesional</label>
                            <div class="input-group">
                                <select name="prefix" class="form-select bg-light fw-bold border-end-0">
                                    <option value="Dr." {{ old('prefix', $doctor->prefix) == 'Dr.' ? 'selected' : '' }}>Dr.</option>
                                    <option value="Dra." {{ old('prefix', $doctor->prefix) == 'Dra.' ? 'selected' : '' }}>Dra.</option>
                                </select>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $doctor->user->name) }}" required>
                            </div>
                        </div>

                        {{-- Sección Firma Digital --}}
                        <div class="col-md-12 mt-4">
                            <label class="form-label fw-bold small text-muted">Firma Digital (Sello)</label>
                            <div class="d-flex flex-column flex-md-row align-items-center gap-4 p-3 border rounded">
                                <div class="text-center bg-white p-2 border rounded" style="min-width: 150px;">
                                    <img id="signature-preview"
                                         src="{{ $doctor->signature_path ? asset('storage/' . $doctor->signature_path) : 'https://via.placeholder.com/150x80?text=Sin+Firma' }}"
                                         class="img-fluid" style="max-height: 80px; object-fit: contain;">
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" id="signature-input" class="form-control form-control-sm" accept="image/png, image/jpeg">
                                    <small class="text-muted" style="font-size: 0.7rem;">Suba una imagen para recortarla y usarla como firma.</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-5 pt-3 border-top">
                            <button type="submit" class="btn btn-primary px-5 shadow fw-bold float-end">
                                <i class="bi bi-save me-2"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cropperModal" tabindex="-1" aria-labelledby="cropperModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="cropperModalLabel">Ajustar Tamaño de Firma</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div style="max-height: 500px; overflow: hidden;">
                    <img id="image-to-crop" src="" style="display: block; max-width: 100%;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="crop-button">Aplicar Recorte</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    let cropper;
    const signatureInput = document.getElementById('signature-input');
    const imageToCrop = document.getElementById('image-to-crop');
    const previewImg = document.getElementById('signature-preview');
    const cropModalElement = document.getElementById('cropperModal');
    const cropModal = new bootstrap.Modal(cropModalElement);
    const cropButton = document.getElementById('crop-button');
    const doctorForm = document.getElementById('doctor-form');

    // Crear el input oculto dinámicamente si no existe
    let hiddenInput = document.querySelector('input[name="signature_cropped"]');
    if (!hiddenInput) {
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'signature_cropped';
        doctorForm.appendChild(hiddenInput);
    }

    // Detectar cambio en el input de archivo
    signatureInput.addEventListener('change', function (e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            const reader = new FileReader();
            reader.onload = function (event) {
                imageToCrop.src = event.target.result;
                cropModal.show();
            };
            reader.readAsDataURL(files[0]);
        }
    });

    // Inicializar Cropper cuando el modal se muestra
    cropModalElement.addEventListener('shown.bs.modal', function () {
        cropper = new Cropper(imageToCrop, {
            aspectRatio: 2 / 1, // Tamaño firma
            viewMode: 1,
            autoCropArea: 1,
        });
    });

    // Destruir Cropper al cerrar el modal
    cropModalElement.addEventListener('hidden.bs.modal', function () {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    });

    // Ejecutar el recorte
    cropButton.addEventListener('click', function () {
        if (cropper) {
            const canvas = cropper.getCroppedCanvas({
                width: 400,
                height: 200,
            });

            const base64Image = canvas.toDataURL('image/png');
            previewImg.src = base64Image;
            hiddenInput.value = base64Image;
            cropModal.hide();
        }
    });
</script>
@endsection
