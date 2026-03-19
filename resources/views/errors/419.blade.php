<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 - Sesión Congelada | {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Source+Code+Pro:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #00051a; /* Un azul muy oscuro, casi negro */
            color: #00d4ff; /* Azul cian estilo criogénico */
            height: 100vh;
            margin: 0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-card {
            background: rgba(0, 20, 40, 0.9);
            border: 1px solid #00d4ff;
            border-radius: 25px;
            padding: 3rem;
            max-width: 650px;
            width: 90%;
            text-align: center;
            box-shadow: 0 0 40px rgba(0, 212, 255, 0.15);
            position: relative;
        }

        .ice-emoji {
            font-size: 6rem;
            display: inline-block;
            animation: freeze 4s infinite;
        }

        @keyframes freeze {
            0%, 100% { transform: scale(1) rotate(0deg); filter: brightness(1); }
            50% { transform: scale(1.1) rotate(5deg); filter: brightness(1.5); }
        }

        .terminal-text {
            font-family: 'Source Code Pro', monospace;
            background: rgba(0, 212, 255, 0.05);
            border-left: 3px solid #00d4ff;
            padding: 15px;
            text-align: left;
            font-size: 0.85rem;
            margin: 1.5rem 0;
        }

        .btn-cyan {
            background: transparent;
            color: #00d4ff;
            border: 2px solid #00d4ff;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-cyan:hover {
            background: #00d4ff;
            color: #000;
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.6);
        }

        /* Nieve cayendo (efecto de frío/tiempo pasado) */
        .snowflake {
            position: absolute;
            top: -10px;
            color: #fff;
            opacity: 0.3;
            pointer-events: none;
            animation: fall linear infinite;
        }

        @keyframes fall {
            to { transform: translateY(100vh); }
        }
    </style>
</head>
<body>

    <div class="error-card">
        <h1 class="display-1 fw-black" style="font-weight: 900; opacity: 0.8;">419</h1>

        <div class="ice-emoji">🧊🦟</div> {{-- El mosquito en ámbar --}}

        <h3 class="fw-bold mt-3">SESIÓN CONGELADA EN ÁMBAR</h3>

        <p class="text-info opacity-75">Tu token de seguridad ha expirado debido a la inactividad.</p>

        <div class="terminal-text">
            <span class="text-white">> Verificando integridad del ADN... [FALLIDO]</span><br>
            <span class="text-info">> Motivo: Tiempo de espera agotado (Token CSRF mismatch).</span><br>
            <span class="text-warning">> El sistema se ha protegido. Por favor, recarga la página para continuar.</span>
        </div>

        <div class="d-grid gap-2 d-sm-flex justify-content-center mt-4">
            <button onclick="location.reload()" class="btn btn-cyan rounded-pill px-5 py-2 fs-5">
                <i class="bi bi-arrow-clockwise me-2"></i> REANIMAR SESIÓN
            </button>
        </div>

        <p class="mt-4 small opacity-50">
            <a href="/" class="text-decoration-none text-info">O vuelve al campamento base (Inicio)</a>
        </p>
    </div>

    <script>
        // Generador de "nieve" o partículas frías
        function createSnowflake() {
            const snow = document.createElement('div');
            snow.className = 'snowflake';
            snow.innerHTML = '•';
            snow.style.left = Math.random() * 100 + 'vw';
            snow.style.animationDuration = (Math.random() * 3 + 2) + 's';
            snow.style.fontSize = Math.random() * 20 + 10 + 'px';
            document.body.appendChild(snow);
            setTimeout(() => snow.remove(), 5000);
        }
        setInterval(createSnowflake, 200);
    </script>
</body>
</html>
