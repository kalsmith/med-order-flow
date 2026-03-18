@extends('layouts.admin')

@section('header', 'Editar Artículo: ' . $post->title)

@section('header-actions')
    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver al blog
    </a>
@endsection

@section('content')

{{-- Editor de texto CKEditor 5 --}}
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<div class="row justify-content-center">
    <div class="col-md-11 col-lg-10">

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
            <div class="bg-primary py-1"></div>

            <div class="card-body p-4 p-md-5">
                <form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        {{-- Sección: Contenido Principal --}}
                        <div class="col-12">
                            <h6 class="text-primary text-uppercase fw-bold small mb-3">
                                <i class="bi bi-journal-text me-2"></i>Contenido del Artículo
                            </h6>
                            <hr class="mt-0 opacity-10">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">Título del Post</label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $post->title) }}" required placeholder="Ej: Importancia del chequeo anual">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">Imagen Destacada (SEO)</label>
                            <div class="d-flex flex-column flex-md-row align-items-center gap-4 p-3 border rounded bg-light">
                                <div class="text-center bg-white p-2 border rounded overflow-hidden" style="min-width: 150px; height: 100px;">
                                    <img id="featured-image-preview" src="{{ $post->image_url }}"
                                         class="img-fluid w-100 h-100" style="object-fit: cover;">
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="featured_image" id="featured-image-input" class="form-control form-control-sm" accept="image/*">
                                    <small class="text-muted" style="font-size: 0.7rem;">Suba una imagen nueva para reemplazar la actual (Máx 2MB).</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Resumen Corto (Aparece en el listado y Google)</label>
                            <textarea name="summary" class="form-control" rows="2" maxlength="500" placeholder="Escribe un extracto de lo que trata el post...">{{ old('summary', $post->summary) }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Contenido Completo</label>
                            <textarea name="content" id="editor" class="form-control">{{ old('content', $post->content) }}</textarea>
                        </div>

                        {{-- SECCIÓN: SEO y Conversión --}}
                        <div class="col-12 mt-5">
                            <h6 class="text-primary text-uppercase fw-bold small mb-3">
                                <i class="bi bi-graph-up-arrow me-2"></i>SEO y Conversión (Ventas)
                            </h6>
                            <hr class="mt-0 opacity-10">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Vincular a un Pack o Examen (CTA)</label>
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
                            <small class="text-muted">Si seleccionas uno, aparecerá una tarjeta de compra al final del post.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Meta Title (Opcional)</label>
                            <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $post->meta_title) }}" placeholder="Título para buscadores">
                        </div>

                        {{-- SECCIÓN: Publicación --}}
                        <div class="col-12 mt-4 p-3 bg-light rounded border">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $post->is_published) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="is_published">Artículo Publicado (Visible en la web)</label>
                                    </div>
                                    @if($post->published_at)
                                        <small class="text-muted">Publicado originalmente el {{ $post->published_at->format('d/m/Y H:i') }} hrs.</small>
                                    @else
                                        <small class="text-muted">Aún no ha sido publicado (Borrador).</small>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary px-5 shadow fw-bold">
                                        <i class="bi bi-save me-2"></i> Actualizar Artículo
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

<script>
    // Inicialización de CKEditor 5
    ClassicEditor
        .create(document.querySelector('#editor'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'undo', 'redo'],
        })
        .catch(error => {
            console.error(error);
        });

    // Previsualización de la imagen destacada al cambiarla
    const input = document.getElementById('featured-image-input');
    const preview = document.getElementById('featured-image-preview');

    input.onchange = function (e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            const reader = new FileReader();
            reader.onload = function (event) {
                preview.src = event.target.result;
            };
            reader.readAsDataURL(files[0]);
        }
    };
</script>

<style>
    /* Ajuste de altura para el editor */
    .ck-editor__editable_inline {
        min-height: 450px;
    }
</style>

@endsection
