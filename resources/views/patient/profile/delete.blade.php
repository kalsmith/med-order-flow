@extends('layouts.app') {{-- O el layout que uses --}}

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-shield-exclamation" style="font-size: 2.5rem;"></i>
                        </div>
                        <h2 class="fw-bold">Configuración de Cuenta</h2>
                        <p class="text-muted">Gestión de privacidad y desactivación de perfil.</p>
                    </div>

                    @if(!session('status'))
                        <div class="alert alert-info border-0 rounded-4 mb-4">
                            <h6 class="fw-bold"><i class="bi bi-info-circle me-2"></i> ¿Qué significa borrar mi cuenta?</h6>
                            <ul class="small mb-0">
                                <li>Tus datos de contacto serán anonimizados.</li>
                                <li>El acceso a tus órdenes actuales se perderá.</li>
                                <li>Tu correo quedará libre para registrar una nueva cuenta si lo deseas.</li>
                            </ul>
                        </div>

                        <div class="d-grid">
                            <form action="{{ route('profile.delete.request') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-lg rounded-pill fw-bold w-100">
                                    Solicitar Código de Desactivación
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="alert alert-success border-0 rounded-4 mb-4 text-center">
                            <i class="bi bi-envelope-check me-2"></i> Código enviado a <strong>{{ auth()->user()->email }}</strong>
                        </div>

                        <form action="{{ route('profile.delete.confirm') }}" method="POST">
                            @csrf
                            <div class="mb-4 text-center">
                                <label class="form-label fw-bold small text-uppercase">Ingresa el código de 6 dígitos</label>
                                <input type="text" name="code" class="form-control form-control-lg text-center fw-bold"
                                       placeholder="X X X X X X" maxlength="6" autofocus required
                                       style="letter-spacing: 5px; font-size: 1.5rem; border-radius: 15px;">
                                @error('code')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger btn-lg rounded-pill fw-bold">
                                    Confirmar Eliminación Definitiva
                                </button>
                                <a href="{{ route('profile.delete.view') }}" class="btn btn-link text-muted fw-bold">Volver atrás</a>
                            </div>
                        </form>
                    @endif

                    <div class="text-center mt-4">
                        <a href="{{ route('patient.orders') }}" class="text-decoration-none small fw-bold text-primary">
                            <i class="bi bi-arrow-left me-1"></i> Volver a mi panel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
