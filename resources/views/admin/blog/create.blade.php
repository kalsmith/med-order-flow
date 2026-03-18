@extends('layouts.admin')

@section('header', 'Crear Nuevo Artículo')

@section('header-actions')
    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver al blog
    </a>
@endsection

@section('content')

{{-- Editor de texto (puedes usar CKEditor o TinyMCE, aquí preparo el espacio) --}}
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
                <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-4">
                        {{-- Sección: Contenido Principal --}}
                        <div class="col-12">
                            <h6 class="text-primary text-uppercase fw-bold small mb-3">
                                <i class="bi bi-journal-text me-2"></i>Contenido del Artículo
                            </h6>
                            <hr class="mt-0 opacity-10">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-bold small text-muted">Título del Post</label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}" required placeholder="Ej: Importancia del chequeo anual">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">Imagen Destacada (SEO)</label>
                            <input type="file" name="featured_image" class="form-control @error('featured_image') is-invalid @enderror" accept="image/*">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Resumen Corto (Aparece en el listado y Google)</label>
                            <textarea name="summary" class="form-control" rows="2" maxlength="500" placeholder="Escribe un extracto de lo que trata el post...">{{ old('summary') }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Contenido Completo</label>
                            <textarea name="content" id="editor" class="form-control">{{ old('content') }}</textarea>
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
                                        <option value="{{ $exam->id }}" {{ old('cta_id') == $exam->id ? 'selected' : '' }}>
                                            [{{ $exam->isProfile() ? 'PACK' : 'EXAMEN' }}] {{ $exam->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <small class="text-muted">Si seleccionas uno, aparecerá una tarjeta de compra al final del post.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Meta Title (Opcional)</label>
                            <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title') }}" placeholder="Título para buscadores">
                        </div>

                        {{-- SECCIÓN: Publicación --}}
                        <div class="col-12 mt-4 p-3 bg-light rounded border">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="is_published">Publicar inmediatamente</label>
                                    </div>
                                    <small class="text-muted">Si está desactivado, se guardará como borrador.</small>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary px-5 shadow fw-bold">
                                        <i class="bi bi-cloud-arrow-up me-2"></i> Guardar Artículo
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
    ClassicEditor
        .create(document.querySelector('#editor'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'undo', 'redo'],
        })
        .catch(error => {
            console.error(error);
        });
</script>

<style>
    .ck-editor__editable_inline {
        min-height: 400px;
    }
</style>

@endsection
