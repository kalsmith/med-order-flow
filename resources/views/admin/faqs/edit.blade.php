@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h3 class="fw-bold mb-4 text-center">Editar Contenido</h3>

                    <form action="{{ route('admin.faqs.update', $faq) }}" method="POST">
                        @csrf @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold small">Pregunta o Título</label>
                                <input type="text" name="question" class="form-control" value="{{ $faq->question }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Categoría</label>
                                <select name="category" class="form-select" required>
                                    <option value="faq" {{ $faq->category == 'faq' ? 'selected' : '' }}>FAQ (Dudas Comunes)</option>
                                    <option value="legal" {{ $faq->category == 'legal' ? 'selected' : '' }}>Legal / Políticas</option>
                                    <option value="otros" {{ $faq->category == 'otros' ? 'selected' : '' }}>Otros</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Orden</label>
                                <input type="number" name="order" class="form-control" value="{{ $faq->order }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small">Respuesta / Contenido detallado</label>
                                <textarea name="answer" class="form-control" rows="6" required>{{ $faq->answer }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch mt-2">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" {{ $faq->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isActive">Contenido activo y visible</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-5">
                            <button type="submit" class="btn btn-primary px-5 rounded-pill">Actualizar</button>
                            <a href="{{ route('admin.faqs.index') }}" class="btn btn-light px-5 rounded-pill border">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
