@extends('layouts.admin')

@section('header', 'Registrar Nuevo Profesional')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="{{ route('admin.doctors.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Nombre Completo</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Email Institucional</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">RUT</label>
                    <input type="text" name="rut" id="rut" class="form-control" placeholder="12.345.678-9" value="{{ old('rut') }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">N° Registro SIS (RNPI)</label>
                    <input type="text" name="rnpi_number" class="form-control" value="{{ old('rnpi_number') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Firma Digital (Imagen)</label>
                    <input type="file" name="signature" class="form-control">
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">Especialidades (Puedes seleccionar varias)</label>
                    <select name="specialties[]" class="form-select" multiple size="5" required>
                        @foreach($specialties as $specialty)
                            <option value="{{ $specialty->id }}">{{ $specialty->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Mantén Ctrl (o Cmd) presionado para elegir más de una.</small>
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-between">
                <a href="{{ route('doctors.index') }}" class="btn btn-light">Cancelar</a>
                <button type="submit" class="btn btn-primary px-5">Guardar Profesional</button>
            </div>
        </form>
    </div>
</div>
@endsection
