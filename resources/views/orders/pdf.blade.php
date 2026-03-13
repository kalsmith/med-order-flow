<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        /* IMPORTANTE: dompdf requiere rutas absolutas del sistema para archivos locales */
        @font-face {
            font-family: 'Roboto';
            src: url("{{ public_path('fonts/Roboto-Regular.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'Roboto';
            src: url("{{ public_path('fonts/Roboto-Bold.ttf') }}") format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        @font-face {
            font-family: 'Roboto';
            src: url("{{ public_path('fonts/Roboto-Black.ttf') }}") format('truetype');
            font-weight: 900;
            font-style: normal;
        }

        @page {
            margin: 0px;
        }

        body {
            font-family: 'Roboto', Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 11px;
            color: #2d3436;
            line-height: 1.4;
            background-color: #ffffff;
        }

        /* Utilidades de Diseño Moderno */
        .text-blue { color: #0d6efd; }
        .text-muted { color: #636e72; }
        .font-black { font-weight: 900; }
        .font-bold { font-weight: bold; }

        .top-bar { height: 6px; background-color: #0d6efd; width: 100%; }

        .header { padding: 45px 50px 25px 50px; }
        .logo { font-size: 28px; font-weight: 900; color: #0d6efd; letter-spacing: -1px; }

        .id-badge {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 12px 18px;
        }

        .document-title {
            padding: 0 50px;
            font-size: 26px;
            font-weight: 900;
            margin-bottom: 40px;
            color: #2d3436;
        }

        /* Estructura de Secciones */
        .section {
            margin: 0 50px 30px 50px;
            border-top: 1px solid #edf2f7;
            padding-top: 20px;
        }

        .section-title {
            width: 130px;
            font-size: 10px;
            font-weight: 900;
            color: #0d6efd;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            vertical-align: top;
        }

        .label { font-size: 9px; color: #b2bec3; text-transform: uppercase; font-weight: bold; margin-bottom: 2px; }
        .value { font-size: 13px; font-weight: bold; color: #2d3436; margin-bottom: 12px; }

        .prestacion-card {
            background-color: #f1f7ff;
            border-left: 4px solid #0d6efd;
            padding: 20px;
            border-radius: 0 8px 8px 0;
        }

        /* Footer Fijo */
        .footer-fixed {
            position: absolute;
            bottom: 0;
            width: 100%;
        }

        .qr-container {
            margin: 0 50px 40px 50px;
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #e9ecef;
        }

        .legal-footer {
            border-top: 1px solid #edf2f7;
            padding: 15px 50px;
            text-align: center;
            font-size: 9px;
            color: #b2bec3;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="top-bar"></div>

<div class="header">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td width="60%">
                <div class="logo">Doctor911</div>
                <div class="text-muted" style="font-size: 10px; margin-top: 5px;">
                    doctor911.cl &bull; contacto@doctor911.cl<br>
                    📞 +56 9 1234 5678
                </div>
            </td>
            <td width="40%">
                <div class="id-badge">
                    <table width="100%">
                        <tr>
                            <td>
                                <div class="label">Emisión</div>
                                <div class="value" style="margin:0;">{{ $order->created_at->format('d/m/Y') }}</div>
                            </td>
                            <td align="right">
                                <div class="label">ID Orden</div>
                                <div class="value text-blue" style="margin:0; font-family: monospace;">#{{ strtoupper(substr($order->id, 0, 8)) }}</div>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="document-title">Orden médica</div>

<div class="section">
    <table width="100%">
        <tr>
            <td class="section-title">Médico Emisor</td>
            <td>
                <div class="value" style="font-size: 16px; margin-bottom: 5px;">Dr. {{ $order->doctor->user->name }}</div>
                <div class="text-muted">
                    RUT: {{ $order->doctor->rut }} | Registro SIS: {{ $order->doctor->rnpi_number }}<br>
                    <span class="text-blue font-bold">
                        @foreach($order->doctor->specialties as $specialty)
                            {{ strtoupper($specialty->name) }}{{ !$loop->last ? ' / ' : '' }}
                        @endforeach
                    </span>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <table width="100%">
        <tr>
            <td class="section-title">Paciente</td>
            <td>
                <table width="100%">
                    <tr>
                        <td width="45%"><div class="label">Nombre</div><div class="value">{{ strtoupper($order->patient->full_name) }}</div></td>
                        <td width="30%"><div class="label">RUT</div><div class="value">{{ $order->patient->rut }}</div></td>
                        <td><div class="label">Edad</div><div class="value">{{ $order->patient->age }} años</div></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <table width="100%">
        <tr>
            <td class="section-title">Prestaciones</td>
            <td>
                <div class="prestacion-card">
                    <div class="value text-blue" style="font-size: 16px; margin-bottom: 5px;">{{ $order->clinical_context }}</div>
                    <div class="text-muted" style="font-style: italic; font-size: 10px;">
                        Esta orden es válida para su uso en centros de salud, laboratorios y toma de muestras a nivel nacional.
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="footer-fixed">
    <div class="qr-container">
        <table width="100%">
            <tr>
                <td width="80">
                    <img src="data:image/png;base64,{{ $qrCode }}" style="width: 80px; height: 80px;">
                </td>
                <td style="padding-left: 20px; vertical-align: middle;">
                    <div class="text-blue font-black" style="font-size: 11px; margin-bottom: 4px;">DOCUMENTO VERIFICADO DIGITALMENTE</div>
                    <div class="text-muted" style="font-size: 10px; line-height: 1.2;">
                        Para confirmar la validez de esta orden médica, escanee el código QR o visite
                        <strong>doctor911.cl/validar</strong> e ingrese el ID de seguimiento.
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="legal-footer">
        CODE TECH DIGITAL SPA • RUT 77.736.856-7 • SANTIAGO, CHILE
    </div>
</div>

</body>
</html>
