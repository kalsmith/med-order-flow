<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Restringido | {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }
        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 500px;
        }
        .gif-container {
            position: relative;
            display: inline-block;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(13, 110, 253, 0.2);
            border: 4px solid white;
        }
        .nedry-gif {
            width: 100%;
            max-width: 350px;
            display: block;
        }
        .magic-word {
            font-weight: 800;
            color: #0d6efd;
            font-size: 1.5rem;
            margin-top: 1.5rem;
            display: block;
        }
        .error-code {
            position: absolute;
            bottom: 10px;
            right: 15px;
            font-family: monospace;
            font-size: 0.7rem;
            color: rgba(255,255,255,0.5);
            background: rgba(0,0,0,0.3);
            padding: 2px 8px;
            border-radius: 4px;
        }
    </style>
</head>
<body>

    <div class="error-container animate__animated animate__fadeIn">
        {{-- Logo para mantener branding --}}
        <div class="mb-5">
            <img src="{{ asset('assets/logo/logo.png') }}" alt="Logo" height="50">
        </div>

        {{-- El momento Nedry --}}
        <div class="gif-container">
            <img src="https://media.giphy.com/media/uOAXda7ZeJJzW/giphy.gif" alt="Ah ah ah!" class="nedry-gif">
            <div class="error-code">SYS_ERR: 403_FORBIDDEN</div>
        </div>

        <span class="magic-word">¡Ah, ah, ah!</span>
        <h4 class="fw-bold mt-2">No has dicho la palabra mágica...</h4>

        <p class="text-muted mt-3">
            Lo sentimos, pero no tienes los permisos necesarios para acceder a este directorio o archivo.
        </p>

        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-4">
            <a href="/" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                <i class="bi bi-house-fill me-2"></i> Ir al Inicio
            </a>
            <button onclick="window.history.back()" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                Volver atrás
            </button>
        </div>

        <div class="mt-5 pt-4">
            <p class="small text-muted opacity-50 text-uppercase letter-spacing-1">
                Security Systems Central Park - v1.0.4
            </p>
        </div>
    </div>

</body>
</html>
