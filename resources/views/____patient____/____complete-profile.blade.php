<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completar Perfil Médico - MedOrder Flow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #0d6efd; }
        body { background-color: #f4f7fe; font-family: 'Inter', sans-serif; color: #334155; }

        .card-profile {
            border: none;
            border-radius: 28px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.04);
            background: #ffffff;
        }

        .step-indicator {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--primary-color);
            font-weight: 700;
        }

        .form-label { color: #475569; margin-bottom: 0.5rem; }

        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            transition: all 0.2s;
        }

        .form-control:focus {
            background-color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        }

        .btn-primary {
            border-radius: 14px;
            padding: 14px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
        }

        .alert-security {
            background-color: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 16px;
            color: #1e40af;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <div class="text-center mb-5">
                <a href="/" class="text-decoration-none fw-bold fs-4 text-primary">
                    <i class="bi bi-droplet-fill me-1"></i> MedOrder Flow
                </a>
            </div>

            <div class="card card-profile">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="step-indicator">Paso 1 de 2</div>
                        <div class="badge rounded-pill bg-light text-primary border border-primary-subtle">Requerido</div>
                    </div>

                    <h2 class="fw-bold mb-3 h3">Datos del Paciente</h2>
                    <p class="text-muted small mb-4">
                        Para emitir una orden médica válida en Chile (Ley 20.584), necesitamos tus datos oficiales de salud.
                    </p>

                    <div class="alert alert-security d-flex align-items-center mb-4 p-3 small">
                        <i class="bi bi-shield-check fs-4 me-3"></i>
                        <div>Tus datos personales se encuentran encriptados y protegidos bajo secreto médico.</div>
                    </div>

                    <form action="{{ route('profile.store') }}" method="POST" id="profileForm">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase">Nombre Completo</label>
                            <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                                   value="{{ old('full_name', auth()->user()->name) }}" required
                                   placeholder="Como aparece en tu cédula">
                            @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-7 mb-3">
                                <label class="form-label small fw-bold text-uppercase">RUT</label>
                                <input type="text" name="rut" id="rutInput" class="form-control @error('rut') is-invalid @enderror"
                                       value="{{ old('rut') }}" required placeholder="12.345.678-k"
                                       oninput="handleRut(this)">
                                @error('rut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-5 mb-3">
                                <label class="form-label small fw-bold text-uppercase">F. Nacimiento</label>
                                <input type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror"
                                       value="{{ old('birth_date') }}" required>
                                @error('birth_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold d-block text-uppercase text-center mb-3">Sexo Biológico</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="gender_biologic" id="male" value="M"
                                       {{ old('gender_biologic') == 'M' ? 'checked' : '' }} required>
                                <label class="btn btn-outline-primary py-2" for="male">Hombre</label>

                                <input type="radio" class="btn-check" name="gender_biologic" id="female" value="F"
                                       {{ old('gender_biologic') == 'F' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary py-2" for="female">Mujer</label>
                            </div>
                            @error('gender_biologic') <div class="text-danger x-small mt-2 text-center">{{ $message }}</div> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm mt-2" onclick="this.disabled=true; this.form.submit();">
                            Guardar y Continuar <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-center mt-5 text-muted x-small" style="font-size: 0.8rem;">
                <i class="bi bi-info-circle me-1"></i> Al continuar, confirmas que los datos ingresados son verídicos. <br>
                La suplantación de identidad es sancionada por la ley.
            </p>
        </div>
    </div>
</div>

<script>
    /**
     * Formateador de RUT Profesional (con puntos y guión)
     */
    function handleRut(input) {
        // Limpiar de cualquier caracter que no sea número o k
        let value = input.value.replace(/[^0-9kK]/g, '');

        if (value.length < 2) {
            input.value = value;
            return;
        }

        // Separar cuerpo y dígito verificador
        let cuerpo = value.slice(0, -1);
        let dv = value.slice(-1).toUpperCase();

        // Formatear cuerpo con puntos
        cuerpo = cuerpo.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");

        // Unir
        input.value = cuerpo + '-' + dv;
    }
</script>

</body>
</html>
