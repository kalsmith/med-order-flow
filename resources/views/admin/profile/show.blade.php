@extends('layouts.admin') {{-- Ajusta al nombre de tu layout --}}

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Configuración de Mi Perfil</h2>
        </div>

        {{-- COLUMNA IZQUIERDA: Información del Usuario --}}
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Información General</h5>
                </div>
                <div class="card-body text-center">
                    {{-- Espacio para futura Foto de Perfil --}}
                    <div class="mb-3">
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="rounded-circle img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                    </div>
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>
                    <span class="badge bg-info text-dark">
                        {{ ucfirst(str_replace('_', ' ', $user->getRoleNames()->first() ?? 'Sin Rol')) }}
                    </span>
                    <hr>
                    <div class="text-start mt-3">
                        <small class="text-muted d-block text-uppercase fw-bold">Miembro desde:</small>
                        <span>{{ $user->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- ZONA DE PELIGRO: Eliminar cuenta --}}
            <div class="card border-danger shadow-sm mb-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-danger mb-0">Gestión de Cuenta</h6>
                        <small class="text-muted">Eliminar mi acceso de forma permanente</small>
                    </div>
                    <a href="{{ route('admin.profile.delete-confirm') }}" class="btn btn-outline-danger btn-sm">
                        Eliminar Cuenta
                    </a>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: Seguridad / Cambio de Contraseña --}}
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary">Seguridad y Acceso</h5>
                </div>
                <div class="card-body">

                    @if (session('success_password'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>¡Éxito!</strong> {{ session('success_password') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.password.update') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Contraseña Actual</label>
                                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="••••••••">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nueva Contraseña</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Mínimo 8 caracteres, incluir letras y números.</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Confirmar Nueva Contraseña</label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••">
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-shield-lock me-1"></i> Actualizar Contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
