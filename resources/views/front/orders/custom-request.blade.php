<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Solicitud Especial</title>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-sm p-4">
            <h2 class="text-primary">¿Qué examen necesitas?</h2>
            <p class="text-muted">Simulación de orden personalizada</p>
            <hr>
            <form action="{{ route('orders.store.public') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Describe tu requerimiento:</label>
                    <textarea name="custom_description" class="form-control" rows="4" placeholder="Ej: Perfil lipídico y vitamina B12" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">Enviar Solicitud ($9.990)</button>
            </form>
            <a href="{{ route('home') }}" class="btn btn-link mt-2 text-muted">Cancelar</a>
        </div>
    </div>
</body>
</html>
