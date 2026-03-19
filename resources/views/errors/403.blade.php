<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Ah, ah, ah! - 403 Access Denied</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&family=Source+Code+Pro:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0d001a; /* El fondo oscuro de tu versión */
            color: #00ff41; /* Verde Matrix */
            height: 100vh;
            margin: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .terminal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle, rgba(13, 110, 253, 0.05) 0%, rgba(0,0,0,0) 70%);
            pointer-events: none;
        }

        .error-card {
            background: rgba(0, 0, 0, 0.8);
            border: 1px solid #00ff41;
            border-radius: 25px;
            padding: 3rem;
            max-width: 700px;
            width: 90%;
            margin: auto;
            text-align: center;
            box-shadow: 0 0 30px rgba(0, 255, 65, 0.2);
            position: relative;
            z-index: 10;
        }

        h1 {
            font-size: 5rem;
            font-weight: 900;
            margin: 0;
            text-shadow: 2px 0 #ff00c1, -2px 0 #00ff41;
            animation: glitch 2s infinite;
        }

        .dino-box {
            position: relative;
            font-size: 6rem;
            margin: 1rem 0;
            display: inline-block;
        }

        .dino {
            display: inline-block;
            animation: spit 3s infinite;
        }

        /* Animaciones Nedry */
        @keyframes glitch {
            0%, 100% { text-shadow: 2px 0 #ff00c1, -2px 0 #00ff41; }
            20% { text-shadow: -2px 0 #ff00c1, 2px 0 #00ff41; }
            50% { text-shadow: 4px 0 #00ff41, -4px 0 #ff00c1; }
        }

        @keyframes spit {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            20% { transform: translateY(-10px) rotate(-5deg); }
            40% { transform: translateY(5px) rotate(5deg); }
        }

        .spit-emoji {
            position: absolute;
            font-size: 2rem;
            color: #32ff6a;
            opacity: 0;
            pointer-events: none;
            animation: fly 1.5s infinite;
        }

        @keyframes fly {
            0% { opacity: 0; transform: translate(0, 0); }
            10% { opacity: 1; }
            100% { opacity: 0; transform: translate(120px, 60px) scale(0.5); }
        }

        .terminal-text {
            font-family: 'Source Code Pro', monospace;
            background: rgba(0, 255, 65, 0.05);
            border-radius: 10px;
            padding: 15px;
            text-align: left;
            font-size: 0.9rem;
            border-left: 3px solid #00ff41;
            margin-top: 1.5rem;
        }

        .btn-matrix {
            background: transparent;
            color: #00ff41;
            border: 2px solid #00ff41;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-matrix:hover {
            background: #00ff41;
            color: #000;
            box-shadow: 0 0 20px #00ff41;
        }
    </style>
</head>
<body>
    <div class="terminal-overlay"></div>

    <div class="error-card">
        {{-- Logo sutil --}}
        <img src="{{ asset('assets/logo/logo.png') }}" alt="Logo" height="40" class="mb-4" style="filter: brightness(0) invert(1) opacity(0.5);">

        <h1>403</h1>
        <div class="fw-bold tracking-widest text-uppercase mb-2" style="letter-spacing: 5px;">Acceso Denegado</div>

        <div class="dino-box">
            <div class="dino">🦖</div>
            <div class="spit-emoji">☣️</div>
        </div>

        <h4 class="fw-bold mt-2 text-white">¡Ah, ah, ah! No has dicho la palabra mágica</h4>

        <div class="terminal-text">
            <span class="text-secondary">> C:\SECURITY\NEDRY_CONTROL...</span><br>
            <span class="text-danger">> Access violation at address 0x403.</span><br>
            <span class="text-white">> No intentes hackear el sistema de exámenes clínicos. El Dilofosaurio ha sido liberado.</span>
        </div>

        <div class="mt-4 d-flex gap-2 justify-content-center">
            <a href="/" class="btn btn-matrix rounded-pill px-4">
                <i class="bi bi-house-door-fill me-2"></i> ABORTAR Y VOLVER
            </a>
            <button onclick="window.history.back()" class="btn btn-outline-light rounded-pill px-4 opacity-50">
                Atrás
            </button>
        </div>
    </div>

    <script>
        // Efecto de escupitajos random del código original
        setInterval(() => {
            const spit = document.createElement('div');
            spit.className = 'spit-emoji';
            spit.textContent = '☣️';
            spit.style.left = (Math.random() * 20 + 40) + '%';
            spit.style.top = (Math.random() * 20 + 40) + '%';
            document.querySelector('.error-card').appendChild(spit);
            setTimeout(() => spit.remove(), 1500);
        }, 2000);
    </script>
</body>
</html>
