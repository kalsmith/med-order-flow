@extends('layouts.guest')

@section('content')
<div class="container d-flex flex-column min-vh-100 justify-content-center align-items-center">
    <div class="card shadow-lg border-0 px-4 py-5 px-md-5" style="max-width: 500px; width: 100%; border-radius: 1.5rem;">

        <div class="text-center mb-5">
            <a href="/">
                <img src="{{ asset('assets/logo/logo.png') }}" alt="Logo" style="max-height: 85px;">
            </a>
            <h3 class="mt-4 fw-bold text-primary">Nueva Contraseña</h3>
            <p class="text-muted fs-6">Estás a un paso de recuperar tu cuenta. Define tu nueva clave de acceso.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
                <ul class="mb-0 small text-start">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            {{-- Token de seguridad (Obligatorio para que Laravel procese el cambio) --}}
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            {{-- Email (A menudo viene precargado por el link del correo) --}}
            <div class="mb-4">
                <label class="form-label fw-bold small text-uppercase text-muted">Confirmar Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 px-3"><i class="bi bi-envelope fs-5"></i></span>
                    <input type="email" name="email" class="form-control form-control-lg border-start-0 ps-0 bg-light @error('email') is-invalid @enderror"
                           value="{{ old('email', $request->email) }}" required autofocus readonly>
                </div>
                <small class="text-muted mt-1 d-block px-1">Verifica que este sea tu correo registrado.</small>
            </div>

            {{-- Nueva Contraseña --}}
            <div class="mb-4">
                <label class="form-label fw-bold small text-uppercase text-muted">Nueva Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 px-3"><i class="bi bi-shield-lock fs-5"></i></span>
                    <input type="password" name="password" id="reset_password"
                           class="form-control form-control-lg border-start-0 ps-0 bg-light @error('password') is-invalid @enderror"
                           required placeholder="Mínimo 8 caracteres">
                    <button class="btn btn-light border border-start-0 px-3 toggle-password" type="button" data-target="reset_password">
                        <i class="bi bi-eye fs-5"></i>
                    </button>
                </div>
            </div>

            {{-- Confirmar Contraseña --}}
            <div class="mb-5">
                <label class="form-label fw-bold small text-uppercase text-muted">Confirmar Nueva Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 px-3"><i class="bi bi-shield-check fs-5"></i></span>
                    <input type="password" name="password_confirmation" id="reset_password_confirmation"
                           class="form-control form-control-lg border-start-0 ps-0 bg-light"
                           required placeholder="Repite la contraseña">
                    <button class="btn btn-light border border-start-0 px-3 toggle-password" type="button" data-target="reset_password_confirmation">
                        <i class="bi bi-eye fs-5"></i>
                    </button>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm rounded-3">
                    Restablecer Contraseña <i class="bi bi-check2-circle ms-2"></i>
                </button>
            </div>
        </form>
    </div>

    <p class="text-muted mt-4 small opacity-75">
        © {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
    </p>
</div>

{{-- Script de los ojitos --}}
<script>
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    });
</script>
@endsection
