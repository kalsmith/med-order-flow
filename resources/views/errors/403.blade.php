<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Ah, ah, ah! Acceso Denegado | {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&family=Source+Code+Pro:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5; /* Un tono más gris para contrastar */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
            /* Fondo sutil de "píxeles" o "red" */
            background-image: radial-gradient(#d1d9e6 1px, transparent 1px);
            background-size: 20px 20px;
        }

        .error-card {
            background: #ffffff;
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.03);
            padding: 3rem;
            max-width: 550px;
            width: 90%;
            text-align: center;
            position: relative;
            animation: cardSlideIn 0.6s ease-out;
        }

        /* GIF Container Mejorado */
        .gif-frame {
            position: relative;
            display: inline-block;
            border-radius: 20px;
            overflow: hidden;
            border: 8px solid #ffffff;
            box-shadow: 0 10px 30px rgba(13, 110, 253, 0.15);
            margin-bottom: 2rem;
        }

        .nedry-gif {
            width: 100%;
            max-width: 380px;
            display: block;
        }

        /* Animación del texto de error */
        .system-alert-text {
            font-family: 'Source Code Pro', monospace;
            color: #dc3545; /* Rojo de Bootstrap */
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            animation: textBlink 1.5s infinite; /* Parpadeo */
            display: block;
            margin-top: 10px;
        }

        /* Palabra Mágica con más diseño */
        .magic-word {
            font-weight: 800;
            color: #0d6efd;
            font-size: 2rem;
            letter-spacing: -1px;
            margin-top: 1rem;
            display: block;
        }

        /* Subtítulo temático */
        .perm-denied-sub {
            font-size: 1.1rem;
            color: #212529;
            font-weight: 700;
            margin-top: 0.5rem;
        }

        /* Botón de volver atrás más sutil */
        .btn-outline-subtle {
            color: #adb5bd;
            border: 1px solid #dee2e6;
            background: transparent;
        }
        .btn-outline-subtle:hover {
            background-color: #f8f9fa;
            color: #495057;
            border-color: #ced4da;
        }

        /* Footer de Sistema retro */
        .retro-sys-footer {
            font-family: 'Source Code Pro', monospace;
            font-size: 0.7rem;
            color: #ced4da;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 3rem;
            opacity: 0.7;
        }

        /* ANIMACIONES CSS */
        @keyframes cardSlideIn {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes textBlink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
    </style>
</head>
<body>

    <div class="error-card">
        {{-- Logo (Mantenido de tu layout) --}}
        <div class="mb-5">
            <img src="{{ asset('assets/logo/logo.png') }}" alt="PideTuExamen" height="45">
        </div>

        {{-- Contenedor del GIF con diseño y parpadeo --}}
        <div class="gif-frame">
            <img src="https://media.giphy.com/media/uOAXda7ZeJJzW/giphy.gif" alt="Ah ah ah!" class="nedry-gif">
        </div>

        {{-- Texto de error parpadeante --}}
        <span class="system-alert-text">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> PROTOCOLO SEGURIDAD ACTIVO <i class="bi bi-exclamation-triangle-fill ms-1"></i>
        </span>

        {{-- Títulos y Mensajes --}}
        <span class="magic-word">¡Ah, ah, ah!</span>
        <h5 class="perm-denied-sub">No has dicho la palabra mágica...</h5>

        <p class="text-muted mt-3 small px-3">
            Lo sentimos, pero tu perfil no tiene los permisos necesarios para acceder a esta sección o archivo. Por favor, verifica tus credenciales o vuelve al inicio.
        </p>

        {{-- Acciones (Ajustadas a tu estilo de botones) --}}
        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-4 pt-2">
            <a href="/" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm fs-6">
                <i class="bi bi-house-fill me-2"></i> Ir al Inicio
            </a>
            <button onclick="window.history.back()" class="btn btn-outline-subtle rounded-pill px-4 fw-bold small">
                <i class="bi bi-arrow-left me-1 small"></i> Volver atrás
            </a>
        </div>

        {{-- Footer Retro --}}
        <div class="retro-sys-footer">
            ERROR 403: ACCESO DENEGADO // SYS_ERR: NEDRY_PROTOCOL_V1.0.4
        </div>
    </div>

</body>
</html>
