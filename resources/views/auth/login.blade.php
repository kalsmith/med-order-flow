@extends('layouts.guest')

@section('content')
<div class="container d-flex flex-column min-vh-100 justify-content-center align-items-center">
    {{-- Aumentamos el max-width a 500px y añadimos más padding (py-5 px-md-5) --}}
    <div class="card shadow-lg border-0 px-4 py-5 px-md-5" style="max-width: 500px; width: 100%; border-radius: 1.5rem;">

        <div class="text-center mb-5">
            <a href="/">
                <img src="{{ asset('assets/logo/logo.png') }}" alt="Logo" style="max-height: 85px;">
            </a>
            <h3 class="mt-4 fw-bold text-primary">Panel de Gestión</h3>
            <p class="text-muted fs-5">Ingresa tus credenciales para continuar</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email con más altura (form-control-lg) --}}
            <div class="mb-4">
                <label class="form-label fw-bold small text-uppercase text-muted">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 px-3"><i class="bi bi-envelope fs-5"></i></span>
                    <input type="email" name="email" class="form-control form-control-lg border-start-0 ps-0 bg-light @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required autofocus placeholder="ejemplo@medflow.cl" style="font-size: 1rem;">
                </div>
            </div>

            {{-- Password con más altura (form-control-lg) --}}
            <div class="mb-4">
                <label class="form-label fw-bold small text-uppercase text-muted">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 px-3"><i class="bi bi-lock fs-5"></i></span>
                    <input type="password" name="password" id="login_password"
                           class="form-control form-control-lg border-start-0 ps-0 bg-light @error('password') is-invalid @enderror"
                           required placeholder="••••••••" style="font-size: 1rem;">
                    <button class="btn btn-light border border-start-0 px-3 toggle-password" type="button" data-target="login_password">
                        <i class="bi bi-eye fs-5"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                    <label class="form-check-label small fw-medium" for="remember_me">
                        Recordar sesión
                    </label>
                </div>
            </div>

            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm rounded-3">
                    Acceder al Panel <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>

            {{-- Movimos el "Olvidaste tu clave" aquí abajo, centrado y más visible --}}
            @if (Route::has('password.request'))
                <div class="text-center mt-3">
                    <a class="text-decoration-none small fw-bold" href="{{ route('password.request') }}">
                        ¿Problemas para entrar? Recuperar contraseña
                    </a>
                </div>
            @endif
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
