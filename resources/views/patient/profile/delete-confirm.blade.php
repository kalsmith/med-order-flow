@extends('layouts.front')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0" style="border-radius: 20px;">
                <div class="card-body p-4 text-center">

                    {{-- Mensajes de Error Generales --}}
                    @if($errors->any())
                        <div class="alert alert-danger shadow-sm mb-4" style="border-radius: 12px;">
                            <ul class="list-unstyled mb-0">
                                @foreach($errors->all() as $error)
                                    <li><i class="bi bi-exclamation-circle me-2"></i>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(!session('status'))
                        {{-- ESTADO 1: Solicitar código --}}
                        <div class="mb-3">
                            <i class="bi bi-shield-exclamation text-warning" style="font-size: 3.5rem;"></i>
                        </div>
                        <h3 class="fw-bold">Eliminar Cuenta</h3>
                        <p class="text-muted px-3">
                            Esta acción es irreversible. Por seguridad, te enviaremos un código de <strong>6 dígitos</strong> a tu correo para confirmar.
                        </p>

                        <form action="{{ route('profile.delete.request') }}" method="POST" id="requestForm">
                            @csrf
                            <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold py-3 shadow-sm btn-submit">
                                <span class="btn-text">Enviar código al correo</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            </button>
                        </form>
                    @else
                        {{-- ESTADO 2: Ingresar código --}}
                        <div class="mb-3">
                            <i class="bi bi-envelope-check text-success" style="font-size: 3.5rem;"></i>
                        </div>
                        <h3 class="fw-bold">Verifica tu correo</h3>
                        <p class="text-muted">
                            Ingresa el código enviado a:<br>
                            <span class="fw-bold text-dark">{{ auth()->user()->email }}</span>
                        </p>

                        <form action="{{ route('profile.delete.execute') }}" method="POST" id="confirmForm">
                            @csrf
                            <div class="mb-4 mt-4">
                                <input type="text"
                                       name="code"
                                       class="form-control form-control-lg text-center fw-bold shadow-sm"
                                       placeholder="000000"
                                       maxlength="6"
                                       pattern="[0-9]*"
                                       inputmode="numeric"
                                       style="letter-spacing: 8px; font-size: 2rem; border-radius: 15px; border: 2px solid #dee2e6;"
                                       required
                                       autofocus>
                            </div>

                            <button type="submit" class="btn btn-danger rounded-pill w-100 fw-bold py-3 shadow-sm btn-submit">
                                <span class="btn-text">Confirmar eliminación definitiva</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            </button>
                        </form>

                        <div class="mt-3">
                            <form action="{{ route('profile.delete.request') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-link text-decoration-none small">
                                    No recibí el código, enviar de nuevo
                                </button>
                            </form>
                        </div>
                    @endif

                    <hr class="my-4 opacity-25">
                    <a href="{{ route('home') }}" class="btn btn-link text-muted fw-bold text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i> Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Pequeño script para evitar doble clic y mostrar feedback de carga
    document.querySelectorAll('.btn-submit').forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('form');
            if(form.checkValidity()) {
                this.querySelector('.btn-text').classList.add('d-none');
                this.querySelector('.spinner-border').classList.remove('d-none');
                this.classList.add('disabled');
            }
        });
    });
</script>
@endsection
