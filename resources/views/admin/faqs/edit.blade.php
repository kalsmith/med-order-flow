@extends('layouts.admin')

{{-- 1. Estilos para que el editor tenga una buena altura --}}
@push('css')
<style>
    .ck-editor__editable_inline {
        min-height: 400px;
    }
    .ck-editor {
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10"> {{-- Ampliado a 10 para mayor comodidad con el editor --}}
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h3 class="fw-bold mb-4 text-center">Editar Contenido Informativo</h3>

                    <form action="{{ route('admin.faqs.update', $faq) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            {{-- Título / Pregunta --}}
                            <div class="col-md-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">Pregunta o Título</label>
                                <input type="text" name="question" class="form-control rounded-3" value="{{ old('question', $faq->question) }}" required>
                            </div>

                            {{-- Categoría --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Categoría</label>
                                <select name="category" class="form-select rounded-3" required>
                                    <option value="faq" {{ $faq->category == 'faq' ? 'selected' : '' }}>FAQ (Dudas Comunes)</option>
                                    <option value="legal" {{ $faq->category == 'legal' ? 'selected' : '' }}>Legal / Políticas</option>
                                    <option value="otros" {{ $faq->category == 'otros' ? 'selected' : '' }}>Otros</option>
                                </select>
                            </div>

                            {{-- Orden --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Orden de prioridad</label>
                                <input type="number" name="order" class="form-control rounded-3" value="{{ old('order', $faq->order) }}" required>
                            </div>

                            {{-- Editor CKEditor --}}
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">Respuesta / Contenido detallado</label>
                                <div class="border rounded-3">
                                    <textarea name="answer" id="editor" class="form-control" required>{{ old('answer', $faq->answer) }}</textarea>
                                </div>
                            </div>

                            {{-- Estado Activo --}}
                            <div class="col-12">
                                <div class="form-check form-switch mt-2">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" {{ $faq->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="isActive">Contenido activo y visible</label>
                                </div>
                            </div>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="d-flex gap-2 mt-5 pt-4 border-top">
                            <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm fw-bold">
                                Actualizar Contenido
                            </button>
                            <a href="{{ route('admin.faqs.index') }}" class="btn btn-light px-5 rounded-pill border">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
    {{-- Ruta corregida para storage (Asegúrate de haber corrido php artisan storage:link) --}}
    <script src="{{ asset('assets/ckeditor/ckeditor.js') }}"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inicialización para CKEditor 5 (si llegaras a usarlo)
        if (typeof ClassicEditor !== 'undefined') {
            ClassicEditor
                .create(document.querySelector('#editor'), {
                    toolbar: {
                        items: [
                            'heading', '|', 'bold', 'italic', 'link', '|',
                            'bulletedList', 'numberedList', '|',
                            'insertTable', 'blockQuote', '|',
                            'undo', 'redo'
                        ]
                    }
                })
                .catch(error => { console.error(error); });
        }

        // Inicialización para CKEditor 4 (La que estás usando actualmente)
        else if (typeof CKEDITOR !== 'undefined') {
            CKEDITOR.replace('editor', {
                height: 400,

                // 1. Evita que el editor borre estilos o clases de Bootstrap
                allowedContent: true,

                // 2. DESACTIVA el codificado de tildes y caracteres especiales
                // Esto hará que en la BD se guarde "médica" y no "m&eacute;dica"
                entities: false,
                basicEntities: false,
                entities_latin: false,
                entities_greek: false,

                // 3. Configuración adicional de pegado (Opcional pero recomendada)
                forcePasteAsPlainText: false, // Permite mantener negritas al pegar
            });
        }
    });
</script>
@endpush
