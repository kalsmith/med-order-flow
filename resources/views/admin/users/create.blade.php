@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle me-3">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h2 class="mb-0 h4 fw-bold text-dark text-uppercase">Nuevo Usuario Administrativo</h2>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4 px-md-5">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            {{-- Nombre --}}
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">Nombre Completo</label>
                                <input type="text" name="name" class="form-control form-control-lg @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required placeholder="Ej: Juan Pérez">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Email --}}
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control form-control-lg @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" required placeholder="juan@pidetuexamen.cl">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Rol --}}
                            <div class="col-md-12 mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">Rol en la Plataforma</label>
                                <select name="role" class="form-select form-select-lg @error('role') is-invalid @enderror" required>
                                    <option value="" selected disabled>Selecciona un rol...</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text mt-2 text-info">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Los permisos se asignarán automáticamente según el rol seleccionado.
                                </div>
                            </div>

                            <hr class="my-4 text-muted opacity-25">

                            {{-- Password --}}
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">Contraseña Inicial</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="user_password" class="form-control form-control-lg @error('password') is-invalid @enderror" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="user_password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Confirmar Password --}}
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">Confirmar Contraseña</label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="user_password_confirmation" class="form-control form-control-lg" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="user_password_confirmation">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary btn-lg px-5 py-3 fw-bold rounded-3">
                                <i class="bi bi-person-plus-fill me-2"></i> Crear Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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
