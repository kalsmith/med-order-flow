<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Confirmar Pack</title>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-sm p-4 text-center">
            <h2 class="text-primary">Confirmar Selección</h2>
            <hr>
            <p class="lead">Estás solicitando: <strong>{{ $exam_type->name }}</strong></p>
            <p>Paciente: {{ $patient->full_name }} (RUT: {{ $patient->rut }})</p>

            <div class="alert alert-info">
                Precio: <strong>${{ number_format($exam_type->base_price, 0, ',', '.') }}</strong>
            </div>

            <form action="{{ route('orders.store.public') }}" method="POST">
                @csrf
                <input type="hidden" name="exam_type_id" value="{{ $exam_type->id }}">
                <button type="submit" class="btn btn-success btn-lg w-100">Simular Pago</button>
            </form>
            <a href="{{ route('home') }}" class="btn btn-link mt-3 text-muted">Volver</a>
        </div>
    </div>
</body>
</html>
