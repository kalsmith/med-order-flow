@extends('layouts.admin')

@section('header', 'Editar Artículo: ' . $post->title)

@section('header-actions')
    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver al blog
    </a>
@endsection

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<style>
    .ck-editor__editable_inline { min-height: 400px; }
    /* Ajuste para que el editor local se vea bien en el card */
    .cke_chrome { border: 1px solid #dee2e6 !important; box-shadow: none !important; border-radius: 8px; overflow: hidden; }
    .img-container { min-height: 400px; background-color: #f7f7f7; }
</style>
@endpush

@section('content')
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

        <div class="card shadow-sm border-0 overflow-hidden rounded-4">
            <div class="bg-primary py-1"></div>

            <div class="card-body p-4 p-md-5">
                <form id="post-form" action="{{ route('admin.posts.update', $post) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Input oculto para la imagen recortada en Base64 --}}
                    <input type="hidden" name="image_cropped" id="image_cropped">

                    <div class="row g-4">
                        {{-- SECCIÓN: Contenido Principal --}}
                        <div class="col-12">
                            <h6 class="text-primary text-uppercase fw-bold small mb-3">
                                <i class="bi bi-journal-text me-2"></i>Contenido del Artículo
                            </h6>
                            <hr class="mt-0 opacity-10">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">Título del Post</label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $post->title) }}" required>
                        </div>

                        {{-- SECCIÓN IMAGEN CON CROPPER --}}
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">Imagen Destacada (SEO 1200x630)</label>
                            <div class="d-flex flex-column align-items-center gap-3 p-3 border rounded-3 bg-light">
                                <div class="preview-container bg-white border rounded overflow-hidden shadow-sm" style="width: 100%; max-width: 600px; aspect-ratio: 1.91 / 1;">
                                    <img id="featured-preview"
                                         src="{{ asset('storage/' . $post->featured_image) }}?v={{ time() }}"
                                         class="w-100 h-100"
                                         style="object-fit: cover;"
                                         onerror="this.src='https://placehold.co/1200x630?text=Sin+Imagen'">
                                </div>
                                <div class="w-100">
                                    <input type="file" id="image-input" class="form-control" accept="image/png, image/jpeg, image/webp">
                                    <small class="text-muted">Tip: Se recomienda formato WebP para mayor velocidad de carga.</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Resumen Corto (Meta Description)</label>
                            <textarea name="summary" class="form-control" rows="2" maxlength="500">{{ old('summary', $post->summary) }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Cuerpo del Artículo</label>
                            <textarea name="content" id="editor" class="form-control">{{ old('content', $post->content) }}</textarea>
                        </div>

                        {{-- SECCIÓN: SEO y Ventas --}}
                        <div class="col-12 mt-5">
                            <h6 class="text-primary text-uppercase fw-bold small mb-3">
                                <i class="bi bi-graph-up-arrow me-2"></i>SEO y Conversión (Ventas)
                            </h6>
                            <hr class="mt-0 opacity-10">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Vincular Producto (CTA)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-cart-plus"></i></span>
                                <select name="cta_id" class="form-select border-primary-subtle fw-bold">
                                    <option value="">-- No vincular producto --</option>
                                    @foreach($examTypes as $exam)
                                        <option value="{{ $exam->id }}" {{ old('cta_id', $post->cta_id) == $exam->id ? 'selected' : '' }}>
                                            [{{ $exam->isProfile() ? 'PACK' : 'EXAMEN' }}] {{ $exam->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Meta Title Customizado</label>
                            <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $post->meta_title) }}">
                        </div>

                        {{-- Botonera --}}
                        <div class="col-12 mt-4 p-3 bg-light rounded border">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $post->is_published) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="is_published">Artículo Publicado</label>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.posts.index') }}" class="btn btn-light px-4 fw-bold">Cancelar</a>
                                    <button type="submit" class="btn btn-primary px-5 shadow fw-bold">
                                        <i class="bi bi-check-lg me-2"></i> Actualizar Post
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CROPPER --}}
<div class="modal fade" id="cropperModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Ajustar Imagen SEO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="img-container">
                    <img id="image-to-crop" src="" style="max-width: 100%; display: block;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary fw-bold" id="crop-button">Aplicar Recorte</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/ckeditor/ckeditor.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
    // 1. CKEDITOR LOCAL CONFIG
    CKEDITOR.replace('editor', {
        height: 450,
        entities: false,
        basicEntities: false,
        entities_latin: false,
        allowedContent: true,
        // Opcional: añade aquí el resto de la toolbar si la necesitas personalizada
    });

    // 2. LÓGICA DE CROPPER
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
        const canvas = cropper.getCroppedCanvas({
            width: 1200,
            height: 630,
            imageSmoothingQuality: 'high',
        });

        const croppedImageDataURL = canvas.toDataURL('image/webp', 0.85);
        preview.src = croppedImageDataURL;
        hiddenInput.value = croppedImageDataURL;
        getModal().hide();
    });
</script>
@endpush
