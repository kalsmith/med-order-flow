@extends('layouts.admin') {{-- Ajusta según tu layout de admin --}}

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold mb-0">Gestión de Contenidos e Información</h1>
        <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-plus-lg"></i> Nuevo Contenido
        </a>
    </div>

    @foreach($faqs->groupBy('category') as $category => $items)
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-light py-3">
                <h5 class="mb-0 fw-bold text-uppercase small text-muted">Categoría: {{ $category }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width: 80px;">Orden</th>
                                <th>Pregunta / Título</th>
                                <th>Estado</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $faq)
                            <tr>
                                <td class="ps-4"><span class="badge bg-secondary opacity-75">{{ $faq->order }}</span></td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $faq->question }}</div>
                                    <small class="text-muted">Slug: {{ $faq->slug }}</small>
                                </td>
                                <td>
                                    @if($faq->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-3">Activo</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 rounded-pill px-3">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.faqs.edit', $faq) }}" class="btn btn-outline-primary btn-sm rounded-start-pill">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST" onsubmit="return confirm('¿Eliminar este contenido?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-end-pill">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
