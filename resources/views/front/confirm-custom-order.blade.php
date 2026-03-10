<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Solicitud - MedOrder Flow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h3 class="fw-bold mb-4">Confirmar Solicitud Especial</h3>

                <div class="bg-light p-3 rounded-3 mb-4 border-start border-primary border-4">
                    <label class="small text-muted text-uppercase fw-bold">Examen solicitado:</label>
                    <p class="mb-0 fw-semibold">{{ $description }}</p>
                </div>

                <ul class="list-group list-group-flush mb-4">
                    <li class="list-group-item d-flex justify-content-between px-0 bg-transparent">
                        <span>Evaluación Médica</span>
                        <span class="fw-bold">${{ number_format($price, 0, ',', '.') }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 bg-transparent">
                        <span>Emisión de Orden Digital</span>
                        <span class="text-success fw-bold">Incluido</span>
                    </li>
                </ul>

                <form action="{{ route('orders.store.public') }}" method="POST">
                    @csrf
                    <input type="hidden" name="custom_description" value="{{ $description }}">

                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow">
                        Pagar ahora y solicitar <i class="bi bi-credit-card ms-2"></i>
                    </button>
                </form>

                <p class="text-center mt-3 small text-muted">
                    Un médico colegiado revisará tu solicitud y emitirá la orden en minutos.
                </p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
