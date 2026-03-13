<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Helvetica', sans-serif;
            color: #2d3748;
            font-size: 11px;
            margin: 0;
            line-height: 1.4;
        }

        /* Colores de Marca */
        .bg-primary { background-color: #0056b3; }
        .text-primary { color: #0056b3; }

        /* Contenedor Principal */
        .container { padding: 40px; }

        /* Header con Estilo */
        .header { margin-bottom: 30px; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px; }
        .logo-txt { font-size: 26px; font-weight: bold; margin: 0; color: #0056b3; letter-spacing: -1px; }
        .logo-sub { font-size: 10px; color: #718096; text-transform: uppercase; margin-top: 2px; }

        .qr-header { float: right; text-align: right; }
        .order-id { font-family: monospace; background: #f8fafc; padding: 4px 8px; border-radius: 4px; border: 1px solid #e2e8f0; font-size: 10px; margin-top: 5px; }

        /* Bloques de Sección Modernos */
        .section { margin-bottom: 25px; clear: both; }
        .section-header {
            background: #f1f5f9;
            padding: 8px 15px;
            border-left: 4px solid #0056b3;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: 15px;
        }

        .data-grid { width: 100%; border-collapse: collapse; }
        .data-label { color: #64748b; font-size: 9px; text-transform: uppercase; font-weight: bold; }
        .data-value { font-size: 13px; font-weight: 600; color: #1e293b; }

        /* El área de la receta (Rp) */
        .rp-box {
            background: #ffffff;
            padding: 10px 15px;
            min-height: 250px;
        }
        .rp-symbol { font-size: 24px; font-weight: bold; color: #0056b3; margin-bottom: 10px; }
        .indications { font-size: 15px; color: #334155; line-height: 1.6; font-weight: 500; }

        /* Footer y Firma */
        .footer { margin-top: 40px; }
        .signature-block { float: right; width: 250px; text-align: center; }
        .signature-img { max-width: 180px; height: auto; margin-bottom: 5px; mix-blend-mode: multiply; }
        .doc-line { border-top: 1px solid #cbd5e0; margin-top: 10px; padding-top: 10px; }

        .validation-box {
            float: left;
            width: 300px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .bottom-bar {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 40px;
            background: #0056b3;
            text-align: center;
            color: white;
            font-size: 9px;
            line-height: 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="qr-header">
                <img src="data:image/png;base64,{{ $qrCode }}" width="70">
                <div class="order-id">ID: {{ strtoupper(substr($order->id, 0, 8)) }}</div>
            </div>
            <div class="logo-area">
                <p class="logo-txt">DOCTOR 911</p>
                <p class="logo-sub">Servicios Médicos Digitales • Telemedicina</p>
            </div>
        </div>

        <div class="section">
            <div class="section-header">Identificación del Paciente</div>
            <table class="data-grid">
                <tr>
                    <td width="60%">
                        <div class="data-label">Nombre Completo</div>
                        <div class="data-value">{{ $order->patient->full_name }}</div>
                    </td>
                    <td width="40%">
                        <div class="data-label">Fecha de Emisión</div>
                        <div class="data-value">{{ $order->created_at->format('d / m / Y') }}</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding-top: 15px;">
                        <div class="data-label">R.U.T</div>
                        <div class="data-value">{{ $order->patient->rut }}</div>
                    </td>
                    <td style="padding-top: 15px;">
                        <div class="data-label">Edad</div>
                        <div class="data-value">{{ $order->patient->age }} años</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-header">Indicaciones Médicas / Rp</div>
            <div class="rp-box">
                <div class="rp-symbol">Rp.</div>
                <div class="indications">
                    {{-- Aquí corregiremos el bug del contenido --}}
                    {!! nl2br(e($order->clinical_context)) !!}
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="signature-block">
                @if($order->doctor->signature_path)
                    <img src="{{ public_path('storage/' . $order->doctor->signature_path) }}" class="signature-img">
                @else
                    <div style="height: 90px;"></div>
                @endif
                <div class="doc-line">
                    <strong style="font-size: 12px;">Dr. {{ $order->doctor->user->name }}</strong><br>
                    <span style="color: #64748b; font-size: 10px;">
                        R.U.T: {{ $order->doctor->rut }}<br>
                        @foreach($order->doctor->specialties as $specialty)
                            {{ $specialty->name }}{{ !$loop->last ? ' / ' : '' }}
                        @endforeach<br>
                        Reg. S.I.S: {{ $order->doctor->rnpi_number }}
                    </span>
                </div>
            </div>

            <div class="validation-box">
                <table width="100%">
                    <tr>
                        <td width="20%"><i class="bi bi-shield-check" style="font-size: 20px; color: #0056b3;"></i></td>
                        <td>
                            <strong style="font-size: 10px; color: #1e293b; display: block; margin-bottom: 3px;">Documento Verificado</strong>
                            <p style="font-size: 8px; color: #64748b; margin: 0;">
                                La autenticidad de esta orden puede ser validada escaneando el código QR o ingresando el ID en <strong>doctor911.cl/verificar</strong>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="bottom-bar">
        Doctor 911 SpA • Contacto: contacto@doctor911.cl • www.doctor911.cl
    </div>
</body>
</html>
