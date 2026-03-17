@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle me-3">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h2 class="mb-0 h4 fw-bold text-dark text-uppercase">Editar Usuario: {{ $user->name }}</h2>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4 px-md-5">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">Nombre Completo</label>
                                <input type="text" name="name" class="form-control form-control-lg @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}" required>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control form-control-lg @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}" required>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">Cambiar Rol</label>
                                <select name="role" class="form-select form-select-lg" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="bg-light p-4 rounded-3 mb-4">
                                <h6 class="fw-bold text-warning small text-uppercase mb-3"><i class="bi bi-shield-lock me-1"></i> Cambio de Contraseña (Opcional)</h6>
                                <p class="text-muted small">Deja estos campos vacíos si no deseas cambiar la contraseña del usuario.</p>

                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-muted">Nueva Contraseña</label>
                                        <input type="password" name="password" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-muted">Confirmar Nueva Contraseña</label>
                                        <input type="password" name="password_confirmation" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-lg px-5 py-3 fw-bold rounded-3">
                                <i class="bi bi-check-circle me-2"></i> Actualizar Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
