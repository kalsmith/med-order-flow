<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>403 - Acceso Denegado, ¡No seas Nedry!</title>
  <style>
    body {
      margin: 0;
      height: 100vh;
      background: #0d001a;
      color: #00ff41;
      font-family: 'Courier New', Courier, monospace;
      overflow: hidden;
      display: flex;
      justify-content: center;
      align-items: center;
      text-align: center;
    }

    .container {
      position: relative;
      max-width: 900px;
      padding: 20px;
    }

    h1 {
      font-size: 6rem;
      margin: 0;
      text-shadow: 0 0 10px #00ff41, 0 0 20px #00ff41;
      animation: glitch 2s infinite;
    }

    .subtitle {
      font-size: 1.8rem;
      margin: 20px 0;
    }

    .dino {
      font-size: 8rem;
      animation: spit 3s infinite;
      display: inline-block;
      transform-origin: center;
    }

    .message {
      font-size: 1.4rem;
      max-width: 600px;
      margin: 30px auto;
      line-height: 1.6;
    }

    .terminal {
      background: rgba(0, 0, 0, 0.6);
      border: 2px solid #00ff41;
      padding: 20px;
      margin-top: 30px;
      text-align: left;
      font-size: 1.1rem;
      max-width: 700px;
      margin-left: auto;
      margin-right: auto;
    }

    @keyframes glitch {
      0%, 100% { text-shadow: 2px 0 #ff00c1, -2px 0 #00ff41; }
      20% { text-shadow: -2px 0 #ff00c1, 2px 0 #00ff41; }
      40% { text-shadow: 2px 0 #00ff41, -2px 0 #ff00c1; }
      60% { text-shadow: -4px 0 #ff00c1, 4px 0 #00ff41; }
      80% { text-shadow: 4px 0 #00ff41, -4px 0 #ff00c1; }
    }

    @keyframes spit {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      20% { transform: translateY(-10px) rotate(-5deg); }
      40% { transform: translateY(5px) rotate(5deg); }
      60%, 80% {
        transform: translateY(0) rotate(0deg);
        content: " spit ";
      }
    }

    .spit {
      position: absolute;
      font-size: 3rem;
      color: #32ff6a;
      opacity: 0;
      pointer-events: none;
      animation: fly 1.5s infinite;
      left: 48%;
      top: 38%;
    }

    @keyframes fly {
      0% { opacity: 0; transform: translate(0, 0); }
      10% { opacity: 1; }
      100% { opacity: 0; transform: translate(120px, 80px) scale(0.5); }
    }

    @media (max-width: 600px) {
      h1 { font-size: 4rem; }
      .dino { font-size: 6rem; }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>403</h1>
    <div class="subtitle">ACCESO DENEGADO</div>

    <div class="dino">🦖</div>
    <div class="spit">☣️</div> <!-- veneno volando -->

    <div class="message">
      ¡Ey, Nedry! ¿Intentando hackear el sistema otra vez?<br>
      No tienes permisos para esta zona restringida.<br>
      Vuelve al comedor o... ¡cuidado con el dilofosaurio!
    </div>

    <div class="terminal">
      C:\> dir /p<br>
      Acceso denegado.<br>
      Usuario no autorizado. Sistema de seguridad activado.<br>
      ... escupitajo venenoso en 3... 2... 1...
    </div>

    <p style="margin-top:40px; font-size:1.1rem;">
      <a href="/" style="color:#00ff41; text-decoration:none;">→ Volver al inicio (antes de que te coma)</a>
    </p>
  </div>

  <script>
    // Pequeña animación extra de "escupitajos" random
    setInterval(() => {
      const spit = document.createElement('div');
      spit.className = 'spit';
      spit.textContent = '☣️';
      spit.style.left = (Math.random() * 40 + 30) + '%';
      spit.style.top = (Math.random() * 20 + 30) + '%';
      document.body.appendChild(spit);
      setTimeout(() => spit.remove(), 2000);
    }, 1800);
  </script>
</body>
</html>
