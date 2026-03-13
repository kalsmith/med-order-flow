@extends('layouts.admin')

{{-- Estilos para el editor --}}
@push('css')
<style>
    .ck-editor__editable_inline {
        min-height: 300px;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10"> {{-- Amplié un poco el ancho para el editor --}}
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h3 class="fw-bold mb-4 text-center">Nuevo Contenido Informativo</h3>

                    <form action="{{ route('admin.faqs.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold small">Pregunta o Título</label>
                                <input type="text" name="question" class="form-control" placeholder="Ej: ¿Tienen política de devolución?" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Categoría</label>
                                <select name="category" class="form-select" required>
                                    <option value="faq">FAQ (Dudas Comunes)</option>
                                    <option value="legal">Legal / Políticas</option>
                                    <option value="otros">Otros</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Orden de visualización</label>
                                <input type="number" name="order" class="form-control" placeholder="Ej: 1">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small">Respuesta / Contenido detallado</label>
                                {{-- ID "editor" añadido aquí --}}
                                <textarea name="answer" id="editor" class="form-control" rows="6">{{ old('answer') }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked>
                                    <label class="form-check-label" for="isActive">Contenido activo y visible</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-5">
                            <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm">Guardar</button>
                            <a href="{{ route('admin.faqs.index') }}" class="btn btn-light px-5 rounded-pill border">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
    {{-- Si lo tienes en public/assets/ --}}
    <script src="{{ asset('assets/ckeditor/ckeditor.js') }}"></script>

    {{-- Si prefieres no arriesgar con la ruta local, puedes usar el CDN de CK5: --}}
    {{-- <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script> --}}

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof ClassicEditor !== 'undefined') {
                ClassicEditor
                    .create(document.querySelector('#editor'), {
                        toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'undo', 'redo']
                    })
                    .catch(error => { console.error(error); });
            } else if (typeof CKEDITOR !== 'undefined') {
                CKEDITOR.replace('editor', {
                    height: 400,
                    removeButtons: 'PasteFromWord'
                });
            }
        });
    </script>
@endpush
