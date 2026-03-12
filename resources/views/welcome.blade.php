<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedOrder Flow - Órdenes Médicas al Instante</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root { --primary-color: #0d6efd; --soft-bg: #f8faff; }
        body { font-family: 'Inter', sans-serif; scroll-behavior: smooth; background-color: #ffffff; color: #212529; }
        .navbar { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }

        /* Hero mejorado */
        .hero-section { padding: 100px 0 80px; background: radial-gradient(circle at 100% 0%, #eef5ff 0%, #ffffff 40%); }
        .hero-title { font-weight: 800; letter-spacing: -2px; line-height: 1.1; }

        /* Contenedores de Sección */
        .section-header { margin-bottom: 2.5rem; display: flex; align-items: center; gap: 12px; }
        .section-header i { font-size: 1.5rem; color: var(--primary-color); background: var(--soft-bg); padding: 10px; border-radius: 12px; }
        .section-header h3 { font-weight: 800; margin-bottom: 0; letter-spacing: -1px; }

        /* Card de PACKS */
        .card-pack {
            border: 1px solid #eef2f7;
            border-radius: 28px;
            transition: all 0.3s ease;
            background: white;
            overflow: hidden;
            height: 100%;
        }
        .card-pack:hover { transform: translateY(-10px); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08); border-color: #d1e3ff; }

        .pack-badge { background: #eef2ff; color: #4338ca; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; padding: 5px 12px; border-radius: 20px; }
        .price-text { font-size: 2rem; font-weight: 800; color: #1e293b; letter-spacing: -1px; }

        /* Chips de Exámenes */
        .exam-chip {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            font-size: 0.85rem;
            color: #475569;
            margin: 3px;
            font-weight: 500;
        }
        .exam-chip i { color: #10b981; margin-right: 6px; }

        /* Card INDIVIDUAL (Compacta) */
        .card-individual {
            border-radius: 18px;
            border: 1px solid #f1f5f9;
            background: #ffffff;
            padding: 1rem;
            transition: all 0.2s;
        }
        .card-individual:hover { background: #f8faff; border-color: var(--primary-color); }
        .btn-mini-select { border-radius: 10px; font-weight: 700; font-size: 0.8rem; }

        /* Card ESPECIAL (Azul) */
        .card-special {
            background: linear-gradient(135deg, #0d6efd 0%, #004dc7 100%);
            color: white;
            border-radius: 28px;
            border: none;
            padding: 2.5rem;
            box-shadow: 0 20px 40px rgba(13, 110, 253, 0.2);
        }
        .btn-white { background: white; color: var(--primary-color); font-weight: 700; border-radius: 15px; padding: 12px; }
        .btn-white:hover { background: #f8faff; color: #004dc7; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light sticky-top border-bottom">
        <div class="container">
            <a class="navbar-brand fw-extrabold text-primary fs-3 d-flex align-items-center" href="/">
                <i class="bi bi-droplet-fill me-2"></i> MedOrderFlow
            </a>
            <div class="ms-auto">
                @auth
                    <a href="{{ route('patient.orders') }}" class="btn btn-light rounded-pill px-4 fw-bold">Mi Panel</a>
                @else
                    <a href="{{ route('auth.google') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Ingresar</a>
                @endauth
            </div>
        </div>
    </nav>

    <header class="hero-section text-center">
        <div class="container">
            <span class="badge bg-primary bg-opacity-10 text-primary py-2 px-3 rounded-pill mb-4 border border-primary border-opacity-25 fw-bold">
                ✨ 100% Online · Firma Digital · Todo Chile
            </span>
            <h1 class="display-3 hero-title text-dark mb-4">
                Tus Exámenes, <br><span class="text-primary">Sin Esperas.</span>
            </h1>
            <p class="lead text-muted mx-auto mb-5" style="max-width: 600px;">
                Obtén tu orden médica oficial firmada por un profesional en minutos.
            </p>
        </div>
    </header>

    <section class="py-5">
        <div class="container">

            {{-- SECCIÓN PACKS --}}
            <div class="section-header">
                <i class="bi bi-collection-fill"></i>
                <h3>Packs Preventivos</h3>
            </div>

            <div class="row g-4 mb-5">
                @foreach($packs as $pack)
                <div class="col-lg-4 col-md-6">
                    <div class="card card-pack p-4">
                        <div class="mb-3">
                            <span class="pack-badge">Perfil Completo</span>
                        </div>
                        <h4 class="fw-bold mb-3">{{ $pack->name }}</h4>

                        <div class="mb-4 flex-grow-1">
                            <p class="text-muted small fw-bold mb-2">Exámenes incluidos:</p>
                            <div class="d-flex flex-wrap">
                                @foreach($pack->children as $child)
                                    <div class="exam-chip"><i class="bi bi-check-circle-fill"></i> {{ $child->name }}</div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-auto pt-4 border-top">
                            <div class="d-flex justify-content-between align-items-end mb-4">
                                <div>
                                    <span class="text-muted small d-block mb-0 fw-bold">Total Pack</span>
                                    <span class="price-text">${{ number_format($pack->base_price, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <a href="{{ route('order.flow', ['type' => 'pack', 'id' => $pack->id]) }}" class="btn btn-primary w-100 py-3 fw-bold rounded-4 shadow-sm">
                                Seleccionar Pack <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="row g-5">
                {{-- COLUMNA IZQUIERDA: INDIVIDUALES --}}
                <div class="col-lg-7">
                    <div class="section-header">
                        <i class="bi bi-search"></i>
                        <h3>Exámenes Frecuentes</h3>
                    </div>
                    <div class="row g-3">
                        @foreach($individuales as $exam)
                        <div class="col-md-6">
                            <div class="card card-individual h-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div style="max-width: 65%;">
                                        <h6 class="fw-bold mb-1 text-truncate">{{ $exam->name }}</h6>
                                        <span class="text-primary fw-bold small">${{ number_format($exam->base_price, 0, ',', '.') }}</span>
                                    </div>
                                    <a href="{{ route('order.flow', ['type' => 'exam', 'id' => $exam->id]) }}" class="btn btn-outline-primary btn-mini-select px-3">
                                        Pedir
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- COLUMNA DERECHA: ESPECIAL --}}
                <div class="col-lg-5">
                    <div class="section-header">
                        <i class="bi bi-magic"></i>
                        <h3>¿No está en la lista?</h3>
                    </div>
                    <div class="card card-special h-100 d-flex flex-column justify-content-center">
                        <h2 class="fw-bold mb-3 text-white">Orden Personalizada</h2>
                        <p class="opacity-90 mb-4">
                            Dinos qué exámenes necesitas y un médico emitirá una orden a tu medida tras revisar tu solicitud.
                        </p>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fw-bold">Valor base</span>
                            <span class="fs-3 fw-extrabold">$9.990</span>
                        </div>
                        <a href="{{ route('order.flow', ['type' => 'personalizada']) }}" class="btn btn-white w-100 shadow-lg">
                            Solicitar a Medida <i class="bi bi-pencil-square ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <footer class="py-5 text-center text-muted border-top mt-5 bg-light">
        <div class="container">
            <p class="small mb-0 fw-bold">© 2026 MedOrder Flow. Todo Chile.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
