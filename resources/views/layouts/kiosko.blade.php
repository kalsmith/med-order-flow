<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Órdenes Médicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .navbar { background: white; border-bottom: 1px solid #e2e8f0; }
        .btn-primary { background-color: #2563eb; border: none; padding: 0.6rem 1.2rem; font-weight: 600; transition: all 0.2s; }
        .btn-primary:hover { background-color: #1d4ed8; transform: translateY(-1px); }
        .card-pack { transition: transform 0.3s ease, box-shadow 0.3s ease; border-radius: 16px; overflow: hidden; }
        .card-pack:hover { transform: translateY(-5px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
        .price-tag { color: #2563eb; font-weight: 800; font-size: 1.5rem; }
        .hero-section { background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); padding: 80px 0; }
    </style>
</head>
<body>

<nav class="navbar sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="/">
            <i class="bi bi-heart-pulse-fill me-2"></i>MedOrder
        </a>
        <div class="d-flex">
            <span class="text-muted small d-none d-md-inline">¿Necesitas ayuda? +56 9 XXXX XXXX</span>
        </div>
    </div>
</nav>

<main>
    @yield('content')
</main>

<footer class="py-5 bg-white border-top mt-5">
    <div class="container text-center">
        <p class="text-muted small">© 2026 MedOrder Chile - Órdenes Médicas Digitales con Firma Electrónica.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
