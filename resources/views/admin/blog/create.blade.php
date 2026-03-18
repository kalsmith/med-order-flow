@extends('layouts.admin')

@section('header', 'Crear Nuevo Artículo')

@section('header-actions')
    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver al blog
    </a>
@endsection

@section('content')

{{-- Librerías Necesarias --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<div class="row justify-content-center">
    <div class="col-md-11 col-lg-10">

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
                <form id="post-form" action="{{ route('admin.posts.store') }}" method="POST">
                    @csrf

                    {{-- Campo oculto para la imagen recortada --}}
                    <input type="hidden" name="image_cropped" id="image_cropped">

                    <div class="row g-4">
                        <div class="col-12">
                            <h6 class="text-primary text-uppercase fw-bold small mb-3">
                                <i class="bi bi-journal-text me-2"></i>Contenido del Artículo
                            </h6>
                            <hr class="mt-0 opacity-10">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">Título del Post</label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}" required placeholder="Ej: Importancia del chequeo anual">
                        </div>

                        {{-- SECCIÓN IMAGEN CON CROPPER --}}
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">Imagen Destacada (Recomendado 1200x630)</label>
                            <div class="d-flex flex-column align-items-center gap-3 p-3 border rounded bg-light">
                                <div class="preview-container bg-white border rounded overflow-hidden shadow-sm" style="width: 100%; max-width: 600px; aspect-ratio: 1.91 / 1;">
                                    <img id="featured-preview"
                                         src="https://via.placeholder.com/1200x630?text=Seleccionar+Imagen"
                                         class="w-100 h-100" style="object-fit: cover;">
                                </div>
                                <div class="w-100">
                                    <input type="file" id="image-input" class="form-control form-control-sm" accept="image/png, image/jpeg, image/webp">
                                    <small class="text-muted">Al seleccionar una imagen, se abrirá el editor para ajustar el formato SEO.</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Resumen Corto</label>
                            <textarea name="summary" class="form-control" rows="2" maxlength="500" placeholder="Escribe un extracto...">{{ old('summary') }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Contenido Completo</label>
                            <textarea name="content" id="editor" class="form-control">{{ old('content') }}</textarea>
                        </div>

                        <div class="col-12 mt-5">
                            <h6 class="text-primary text-uppercase fw-bold small mb-3">
                                <i class="bi bi-graph-up-arrow me-2"></i>SEO y Conversión
                            </h6>
                            <hr class="mt-0 opacity-10">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Vincular Producto (CTA)</label>
                            <select name="cta_id" class="form-select border-primary-subtle fw-bold">
                                <option value="">-- No vincular producto --</option>
                                @foreach($examTypes as $exam)
                                    <option value="{{ $exam->id }}" {{ old('cta_id') == $exam->id ? 'selected' : '' }}>
                                        [{{ $exam->isProfile() ? 'PACK' : 'EXAMEN' }}] {{ $exam->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Meta Title (SEO)</label>
                            <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title') }}">
                        </div>

                        <div class="col-12 mt-4 p-3 bg-light rounded border">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="is_published">Publicar Artículo</label>
                                </div>
                                <button type="submit" class="btn btn-primary px-5 shadow fw-bold">
                                    <i class="bi bi-save me-2"></i> Crear Artículo
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DE RECORTE --}}
<div class="modal fade" id="cropperModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajustar Imagen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="img-container">
                    <img id="image-to-crop" src="" style="max-width: 100%; display: block;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="crop-button">Cortar y Usar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    // CKEditor
    ClassicEditor.create(document.querySelector('#editor')).catch(error => console.error(error));

    // Cropper Logic
    let cropper;
    const input = document.getElementById('image-input');
    const imageToCrop = document.getElementById('image-to-crop');
    const preview = document.getElementById('featured-preview');
    const modalElement = document.getElementById('cropperModal');
    const hiddenInput = document.getElementById('image_cropped');
    const getModal = () => bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);

    input.onchange = function (e) {
        if (e.target.files && e.target.files.length > 0) {
            const reader = new FileReader();
            reader.onload = function (event) {
                imageToCrop.src = event.target.result;
                getModal().show();
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    };

    modalElement.addEventListener('shown.bs.modal', function () {
        cropper = new Cropper(imageToCrop, {
            aspectRatio: 1200 / 630,
            viewMode: 1,
            autoCropArea: 1,
        });
    });

    modalElement.addEventListener('hidden.bs.modal', function () {
        if (cropper) { cropper.destroy(); cropper = null; }
        input.value = '';
    });

    document.getElementById('crop-button').addEventListener('click', function () {
        const canvas = cropper.getCroppedCanvas({ width: 1200, height: 630 });
        const croppedImageDataURL = canvas.toDataURL('image/webp', 0.85);
        preview.src = croppedImageDataURL;
        hiddenInput.value = croppedImageDataURL;
        getModal().hide();
    });
</script>

<style>
    .ck-editor__editable_inline { min-height: 400px; }
    .img-container { min-height: 400px; background-color: #333; }
</style>

@endsection
