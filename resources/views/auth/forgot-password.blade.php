@extends('layouts.guest')

@section('content')
<div class="container d-flex flex-column min-vh-100 justify-content-center align-items-center">
    <div class="card shadow-lg border-0 px-4 py-5 px-md-5" style="max-width: 500px; width: 100%; border-radius: 1.5rem;">

        <div class="text-center mb-4">
            <a href="/">
                <img src="{{ asset('assets/logo/logo.png') }}" alt="Logo" style="max-height: 85px;">
            </a>
            <h3 class="mt-4 fw-bold text-primary">Recuperar Acceso</h3>
            <p class="text-muted small px-3">
                ¿Olvidaste tu contraseña? No hay problema. Dinós tu correo y te enviaremos un enlace para crear una nueva.
            </p>
        </div>

        {{-- Estado del envío del link --}}
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm mb-4 fw-medium text-center" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-4">
                <label class="form-label fw-bold small text-uppercase text-muted">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 px-3"><i class="bi bi-envelope fs-5 text-muted"></i></span>
                    <input type="email" name="email" class="form-control form-control-lg border-start-0 ps-0 bg-light @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required autofocus placeholder="tu-correo@ejemplo.com">
                </div>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm rounded-3">
                    Enviar Enlace de Recuperación <i class="bi bi-send-fill ms-2"></i>
                </button>
            </div>

            <div class="text-center">
                <a class="text-decoration-none small fw-bold" href="{{ route('login') }}">
                    <i class="bi bi-arrow-left me-1"></i> Volver al Inicio de Sesión
                </a>
            </div>
        </form>
    </div>

    <p class="text-muted mt-4 small opacity-75">
        © {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
    </p>
</div>
@endsection
