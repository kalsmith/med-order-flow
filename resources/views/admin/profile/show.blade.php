@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Configuración de Mi Perfil</h2>
        </div>

        {{-- COLUMNA IZQUIERDA: Información del Usuario --}}
        <div class="col-md-4">
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold">Información General</h5>
                </div>
                <div class="card-body text-center">
                    {{-- Foto de Perfil (UI-Avatars o Profile Photo de Jetstream) --}}
                    <div class="mb-3">
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="rounded-circle img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                    </div>
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-3">{{ $user->email }}</p>

                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                        <i class="bi bi-person-badge me-1"></i>
                        {{ ucfirst(str_replace('_', ' ', $user->getRoleNames()->first() ?? 'Sin Rol')) }}
                    </span>

                    <hr class="my-4 text-muted opacity-25">

                    <div class="text-start mt-3 px-3">
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.75rem;">Miembro desde</small>
                        <span class="text-dark">{{ $user->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: Seguridad / Cambio de Contraseña --}}
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 text-primary fw-bold">Seguridad y Acceso</h5>
                </div>
                <div class="card-body">

                    @if (session('success_password'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <strong>¡Actualizado!</strong> {{ session('success_password') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.password.update') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <label class="form-label fw-semibold">Contraseña Actual</label>
                                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="••••••••">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Nueva Contraseña</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text mt-2">
                                    <i class="bi bi-info-circle me-1"></i> Mínimo 8 caracteres, con letras y números.
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Confirmar Nueva Contraseña</label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••">
                            </div>
                        </div>

                        <div class="text-end mt-2">
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                                <i class="bi bi-shield-lock me-2"></i> Guardar Nueva Contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
