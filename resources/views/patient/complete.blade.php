<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Completar Perfil</title>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow p-4">
                    <h3 class="text-center">Datos del Paciente</h3>
                    <p class="text-muted text-center small">Necesitamos tu RUT para emitir órdenes válidas.</p>
                    <hr>
                    <form action="{{ route('profile.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Tu RUT (Ej: 12.345.678-9)</label>
                            <input type="text" name="rut" class="form-control" placeholder="12.345.678-9" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono (Opcional)</label>
                            <input type="text" name="phone" class="form-control" placeholder="+569...">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg">Guardar y Continuar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
