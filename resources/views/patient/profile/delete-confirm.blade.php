@extends('layouts.front')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0" style="border-radius: 20px;">
                <div class="card-body p-4 text-center">

                    @if(!session('status'))
                        {{-- ESTADO 1: Solicitar código --}}
                        <i class="bi bi-shield-exclamation text-warning mb-3" style="font-size: 3rem;"></i>
                        <h3 class="fw-bold">Eliminar Cuenta</h3>
                        <p class="text-muted">Por seguridad, te enviaremos un código de 6 dígitos a tu correo para confirmar esta acción.</p>

                        <form action="{{ route('profile.delete.request') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold py-2">
                                Enviar código al correo
                            </button>
                        </form>
                    @else
                        {{-- ESTADO 2: Ingresar código --}}
                        <i class="bi bi-envelope-check text-success mb-3" style="font-size: 3rem;"></i>
                        <h3 class="fw-bold">Verifica tu correo</h3>
                        <p class="text-muted">Ingresa el código de 6 dígitos enviado a <strong>{{ auth()->user()->email }}</strong></p>

                        <form action="{{ route('profile.delete.execute') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <input type="text" name="code" class="form-control form-control-lg text-center fw-bold"
                                       placeholder="000000" maxlength="6" style="letter-spacing: 10px;" required>
                                @error('code') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <button type="submit" class="btn btn-danger rounded-pill w-100 fw-bold py-2">
                                Confirmar eliminación definitiva
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('home') }}" class="btn btn-link text-muted mt-3">Cancelar</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
