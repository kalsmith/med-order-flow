@extends('layouts.guest') {{-- Asegúrate de que este layout sea simple --}}

@section('content')
<div class="container d-flex flex-column min-vh-100 justify-content-center align-items-center">
    <div class="card shadow-lg border-0 px-4 py-5" style="max-width: 450px; width: 100%; border-radius: 1.25rem;">

        <div class="text-center mb-4">
            {{-- TU LOGO --}}
            <a href="/">
                <img src="{{ asset('assets/logo/logo.png') }}" alt="Logo" style="max-height: 70px;">
            </a>
            <h4 class="mt-4 fw-bold text-primary">Panel de Gestión</h4>
            <p class="text-muted">Ingresa tus credenciales para continuar</p>
        </div>

        {{-- ALERTAS DE ERROR: Indica si se equivocó --}}
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm mb-4 small" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" name="email" class="form-control border-start-0 ps-0 bg-light @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required autofocus placeholder="ejemplo@medflow.cl">
                </div>
            </div>

            {{-- Password --}}
            <div class="mb-1">
                <div class="d-flex justify-content-between">
                    <label class="form-label fw-semibold">Contraseña</label>
                    @if (Route::has('password.request'))
                        <a class="small fw-bold text-decoration-none" href="{{ route('password.request') }}">
                            ¿Olvidaste tu clave?
                        </a>
                    @endif
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" id="login_password"
                           class="form-control border-start-0 ps-0 bg-light @error('password') is-invalid @enderror"
                           required placeholder="••••••••">
                    <button class="btn btn-light border-start-0 toggle-password" type="button" data-target="login_password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            {{-- Recordarme --}}
            <div class="mb-4 mt-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                    <label class="form-check-label small text-muted" for="remember_me">
                        Mantener sesión iniciada
                    </label>
                </div>
            </div>

            {{-- Botón Ingresar --}}
            <div class="d-grid">
                <button type="submit" class="btn btn-primary py-2 fw-bold shadow-sm">
                    Acceder al Panel <i class="bi bi-arrow-right-short ms-1"></i>
                </button>
            </div>
        </form>
    </div>

    <p class="text-muted mt-4 small">
    © {{ date('Y') }} {{ config('app.name', 'Laravel') }}. Todos los derechos reservados.
</p>
</div>

{{-- Reutilizamos el script de los ojitos --}}
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
