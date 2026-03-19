<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Recinto Vacío | {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Source+Code+Pro:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #1a1a00; /* Fondo amarillo muy oscuro */
            color: #ffcc00; /* Amarillo precaución neón */
            height: 100vh;
            margin: 0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Fondo de vallas eléctricas */
            background-image: radial-gradient(#333300 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .error-card {
            background: rgba(40, 40, 0, 0.9);
            border: 2px solid #ffcc00;
            border-radius: 25px;
            padding: 3rem;
            max-width: 700px;
            width: 90%;
            text-align: center;
            box-shadow: 0 0 40px rgba(255, 204, 0, 0.2);
            position: relative;
            animation: warningFlicker 3s infinite; /* Parpadeo de luz de precaución */
        }

        h1 {
            font-size: 6rem;
            font-weight: 900;
            margin: 0;
            text-shadow: 0 0 15px #ffcc00, 0 0 30px #ffcc00;
        }

        .escape-emoji {
            font-size: 6rem;
            display: inline-block;
            animation: walkAway 5s infinite linear; /* El T-Rex alejándose */
        }

        @keyframes warningFlicker {
            0%, 100% { opacity: 1; border-color: #ffcc00; box-shadow: 0 0 40px rgba(255, 204, 0, 0.2); }
            50% { opacity: 0.8; border-color: #ffff99; box-shadow: 0 0 60px rgba(255, 204, 0, 0.4); }
        }

        @keyframes walkAway {
            0% { transform: translateX(-20px) scale(1); opacity: 1; }
            50% { transform: translateX(20px) scale(0.9); opacity: 0.7; }
            100% { transform: translateX(60px) scale(0.8); opacity: 0; }
        }

        .terminal-text {
            font-family: 'Source Code Pro', monospace;
            background: rgba(255, 204, 0, 0.05);
            border-left: 3px solid #ffcc00;
            padding: 15px;
            text-align: left;
            font-size: 0.85rem;
            margin: 2rem 0;
        }

        .btn-warning-neon {
            background: transparent;
            color: #ffcc00;
            border: 2px solid #ffcc00;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-warning-neon:hover {
            background: #ffcc00;
            color: #000;
            box-shadow: 0 0 25px rgba(255, 204, 0, 0.7);
        }

        /* Huellas de T-Rex */
        .footprint {
            position: absolute;
            font-size: 3rem;
            color: #333300;
            opacity: 0.5;
            pointer-events: none;
            animation: trackAppear 5s infinite;
        }

        @keyframes trackAppear {
            0%, 100% { opacity: 0; }
            10%, 90% { opacity: 0.5; }
        }
    </style>
</head>
<body>
    <div class="error-card">
        <h1 class="display-1 fw-black" style="font-weight: 900;">404</h1>

        <div class="escape-emoji">🚧🦖🐾</div> {{-- Valla, T-Rex y huellas --}}

        <h3 class="fw-bold mt-3 text-white">RECINTO VACÍO - OBJETIVO NO ENCONTRADO</h3>

        <p class="text-warning fw-bold fs-5 blink">CAUTION: ASSET_OFFLINE</p>

        <div class="terminal-text text-warning">
            <span class="text-white">C:\SYSTEM\TRACKING_UNIT_04... [FALLIDO]</span><br>
            <span class="text-white">C:\SYSTEM\DINO_SENSORS... [NO_DATA]</span><br>
            <span class="text-white">> No hemos podido encontrar la página o examen que buscas.</span><br>
            <span class="text-warning">> El Dilofosaurio o el T-Rex podrían habérselo comido.</span><br>
            <span class="text-warning">> Vuelve a una zona segura antes de que te encuentren.</span>
        </div>

        <div class="d-grid gap-2 d-sm-flex justify-content-center mt-4 pt-2">
            <a href="/" class="btn btn-warning-neon btn-lg rounded-pill px-5 fs-6 shadow-sm">
                <i class="bi bi-house-door-fill me-2"></i> IR A ZONA SEGURA (INICIO)
            </a>
            <button onclick="window.history.back()" class="btn btn-outline-light rounded-pill px-4 fw-bold small opacity-75">
                <i class="bi bi-arrow-left me-1"></i> Volver atrás
            </button>
        </div>

        <p class="mt-4 small opacity-50 text-uppercase letter-spacing-1 text-warning">
            Security Systems Central Park - v1.0.4 // 404_ESCAPE_PROTOCOL
        </p>
    </div>

    <script>
        // Generador de huellas de T-Rex
        function createFootprint() {
            const footprint = document.createElement('div');
            footprint.className = 'footprint bi bi-signpost-split-fill'; // Usando un icono de Bootstrap como huella abstracta
            footprint.style.left = (Math.random() * 80 + 10) + 'vw';
            footprint.style.top = (Math.random() * 80 + 10) + 'vh';
            footprint.style.transform = 'rotate(' + (Math.random() * 360) + 'deg)';
            document.body.appendChild(footprint);
            setTimeout(() => footprint.remove(), 5000);
        }
        setInterval(createFootprint, 1000);
    </script>
</body>
</html>
