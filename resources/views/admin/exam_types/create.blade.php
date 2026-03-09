@extends('layouts.admin')

@section('header', 'Nuevo Examen o Pila')

@section('header-actions')
    <a href="{{ route('admin.exam-types.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <form action="{{ route('admin.exam-types.store') }}" method="POST">
            @csrf

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4 text-dark fw-bold">Información General</h5>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Nombre del Examen o Pack</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Ej: Perfil Bioquímico" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Especialidad</label>
                            <select name="specialty_id" class="form-select @error('specialty_id') is-invalid @enderror" required>
                                <option value="">Seleccione...</option>
                                @foreach($specialties as $specialty)
                                    <option value="{{ $specialty->id }}" {{ old('specialty_id') == $specialty->id ? 'selected' : '' }}>
                                        {{ $specialty->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('specialty_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Código Fonasa (Padre)</label>
                            <input type="text" name="code_fonasa" class="form-control" value="{{ old('code_fonasa') }}" placeholder="0404001">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Precio de Venta ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="base_price" class="form-control @error('base_price') is-invalid @enderror"
                                       value="{{ old('base_price', 0) }}" required>
                            </div>
                            <small class="text-muted">Si es una pila, este es el cobro total.</small>
                            @error('base_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sección de Composición (Pila) --}}
            <div class="card shadow-sm border-0 border-start border-primary border-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-stack fs-4 text-primary me-2"></i>
                        <h5 class="card-title m-0 text-dark fw-bold">Definir como Pila (Opcional)</h5>
                    </div>
                    <p class="text-muted small mb-4">
                        Si este nuevo registro es una batería de exámenes, selecciona aquí sus componentes.
                        <strong>Nota:</strong> Solo aparecen exámenes ya existentes en el sistema.
                    </p>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Exámenes incluidos en esta batería</label>
                        <select name="bundle_ids[]" class="form-select @error('bundle_ids') is-invalid @enderror" multiple style="min-height: 180px;">
                            @foreach($allExams as $item)
                                <option value="{{ $item->id }}" {{ (is_array(old('bundle_ids')) && in_array($item->id, old('bundle_ids'))) ? 'selected' : '' }}>
                                    {{ $item->name }} {{ $item->code_fonasa ? "($item->code_fonasa)" : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text mt-2 text-info">
                            <i class="bi bi-info-circle me-1"></i> Mantén presionado Ctrl/Cmd para seleccionar varios.
                        </div>
                        @error('bundle_ids') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-5 shadow-sm">
                    <i class="bi bi-check-lg me-2"></i> Crear Examen / Pack
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
