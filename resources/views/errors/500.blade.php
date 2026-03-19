<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Falla Crítica del Sistema | {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Source+Code+Pro:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #1a0000; /* Fondo rojo muy oscuro */
            color: #ff3333; /* Rojo neón de peligro */
            height: 100vh;
            margin: 0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Fondo sutil de vallas eléctricas rotas */
            background-image:
                linear-gradient(rgba(255, 0, 0, 0.05) 1px, transparent 1px),
                linear-gradient(90s, rgba(255, 0, 0, 0.05) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        .error-card {
            background: rgba(40, 0, 0, 0.9);
            border: 2px solid #ff3333;
            border-radius: 25px;
            padding: 3rem;
            max-width: 700px;
            width: 90%;
            text-align: center;
            box-shadow: 0 0 50px rgba(255, 51, 51, 0.2);
            position: relative;
            animation: redAlert 2s infinite; /* Parpadeo de luz roja de emergencia */
        }

        h1 {
            font-size: 6rem;
            font-weight: 900;
            margin: 0;
            text-shadow: 0 0 15px #ff3333, 0 0 30px #ff3333;
        }

        .broken-emoji {
            font-size: 6rem;
            display: inline-block;
            animation: shake 0.5s infinite;
        }

        @keyframes redAlert {
            0%, 100% { box-shadow: 0 0 50px rgba(255, 51, 51, 0.2); border-color: #ff3333; }
            50% { box-shadow: 0 0 80px rgba(255, 51, 51, 0.5); border-color: #ffcccc; }
        }

        @keyframes shake {
            0% { transform: translate(1px, 1px) rotate(0deg); }
            10% { transform: translate(-1px, -2px) rotate(-1deg); }
            30% { transform: translate(3px, 2px) rotate(0deg); }
            50% { transform: translate(-1px, -1px) rotate(1deg); }
            80% { transform: translate(-1px, 1px) rotate(-1deg); }
            100% { transform: translate(1px, -2px) rotate(-1deg); }
        }

        .terminal-text {
            font-family: 'Source Code Pro', monospace;
            background: rgba(255, 0, 0, 0.1);
            border-left: 3px solid #ff3333;
            padding: 15px;
            text-align: left;
            font-size: 0.85rem;
            margin: 2rem 0;
            max-height: 200px;
            overflow-y: auto; /* Para simular una consola llena */
        }

        .btn-red {
            background: transparent;
            color: #ff3333;
            border: 2px solid #ff3333;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-red:hover {
            background: #ff3333;
            color: #000;
            box-shadow: 0 0 25px rgba(255, 51, 51, 0.7);
        }

        /* Líneas de escaneo de TV antigua */
        .scanlines {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.2) 50%);
            background-size: 100% 4px;
            pointer-events: none;
            opacity: 0.15;
        }
    </style>
</head>
<body>
    <div class="scanlines"></div>

    <div class="error-card">
        <h1 class="display-1 fw-black" style="font-weight: 900;">500</h1>

        <div class="broken-emoji">🚧💣💀</div> {{-- Vallas rotas, bomba y calavera --}}

        <h3 class="fw-bold mt-3 text-white">FALLA TOTAL DEL SISTEMA CENTRAL</h3>

        <p class="text-danger fw-bold fs-5 blink">RED_ALERT: CRITICAL_SERVER_FAILURE</p>

        <div class="terminal-text text-danger">
            <span class="text-white">C:\SYSTEM\SECURITY_MAIN... [OK]</span><br>
            <span class="text-white">C:\SYSTEM\POWER_GRID... [FALLIDO]</span><br>
            <span class="text-white">C:\SYSTEM\DINO_FENCES... [INOPERATIVO]</span><br>
            <span class="text-white">> KERNAL PANIC: Excepción inesperada en el servidor.</span><br>
            <span class="text-warning">> Ray Arnold está intentando reiniciar los sistemas manualmente.</span><br>
            <span class="text-warning">> Por favor, espera unos minutos o inténtalo más tarde.</span>
        </div>

        <div class="d-grid gap-2 d-sm-flex justify-content-center mt-4 pt-2">
            <button onclick="location.reload()" class="btn btn-red btn-lg rounded-pill px-5 fs-6 shadow-sm">
                <i class="bi bi-arrow-clockwise me-2"></i> INTENTAR REINICIO
            </button>
        </div>

        <p class="mt-4 small opacity-50 text-uppercase letter-spacing-1">
            <a href="/" class="text-decoration-none text-danger">O vuelve a la base principal (Inicio)</a>
        </p>
    </div>

</body>
</html>
