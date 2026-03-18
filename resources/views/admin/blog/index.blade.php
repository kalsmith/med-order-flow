@extends('layouts.admin')

@section('header', 'Gestión del Blog')

@section('header-actions')
    <a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-sm shadow-sm px-3">
        <i class="bi bi-plus-lg me-1"></i> Crear Nuevo Artículo
    </a>
@endsection

@section('content')

<div class="row">
    <div class="col-12">

        {{-- Alerta de Éxito --}}
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted uppercase small fw-bold">
                            <tr>
                                <th class="px-4 py-3" style="width: 80px;">Imagen</th>
                                <th class="py-3">Artículo / Autor</th>
                                <th class="py-3">Conversión (CTA)</th>
                                <th class="py-3 text-center">Estado</th>
                                <th class="py-3 text-center">Fecha Pub.</th>
                                <th class="px-4 py-3 text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($posts as $post)
                                <tr>
                                    <td class="px-4">
                                        <div class="rounded bg-light overflow-hidden" style="width: 60px; height: 40px;">
                                            <img src="{{ $post->image_url }}" class="w-100 h-100" style="object-fit: cover;">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $post->title }}</div>
                                        <div class="text-muted small">
                                            <i class="bi bi-person me-1"></i> {{ $post->author->name }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($post->cta_id)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                                <i class="bi bi-cart-check me-1"></i>
                                                {{ Str::limit($post->examType->name, 25) }}
                                            </span>
                                        @else
                                            <span class="text-muted small italic">Sin vincular</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($post->is_published)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                                                <i class="bi bi-eye me-1"></i> Publicado
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1">
                                                <i class="bi bi-pencil me-1"></i> Borrador
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center text-muted small">
                                        {{ $post->published_at ? $post->published_at->format('d/m/Y H:i') : '---' }}
                                    </td>
                                    <td class="px-4 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            {{-- Botón Ver (Frontend) --}}
                                            {{-- <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="btn btn-outline-light text-dark btn-sm border shadow-sm" title="Ver en la web">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a> --}}

                                            {{-- Botón Editar --}}
                                            <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-outline-primary btn-sm shadow-sm">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            {{-- Botón Eliminar --}}
                                            <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este artículo? Esta acción no se puede deshacer.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm shadow-sm">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-journal-x display-4 mb-3 d-block"></i>
                                            Aún no has escrito ningún artículo. <br>
                                            <a href="{{ route('admin.posts.create') }}" class="fw-bold">¡Empieza a escribir ahora!</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="p-4 border-top">
                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table thead th {
        font-size: 0.75rem;
        letter-spacing: 0.05rem;
        border-bottom: 0;
    }
    .badge {
        font-weight: 600;
        border-radius: 6px;
    }
    .btn-outline-light:hover {
        background-color: #f8f9fa;
    }
</style>

@endsection
