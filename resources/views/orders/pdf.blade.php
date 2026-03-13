<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        /* Optimización de fuentes para dompdf */
        @font-face {
            font-family: 'Roboto';
            src: url("{{ public_path('fonts/Roboto-Regular.ttf') }}") format('truetype');
            font-weight: normal;
        }
        @font-face {
            font-family: 'Roboto';
            src: url("{{ public_path('fonts/Roboto-Bold.ttf') }}") format('truetype');
            font-weight: bold;
        }

        @page { margin: 0px; }

        body {
            font-family: 'Roboto', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0 0 180px 0; /* Reserva espacio para el footer absoluto */
            font-size: 11px;
            color: #2d3436;
            line-height: 1.4;
            background-color: #ffffff;
        }

        .top-bar { height: 6px; background-color: #0d6efd; width: 100%; }
        .header { padding: 45px 50px 25px 50px; }
        .logo { font-size: 28px; font-weight: bold; color: #0d6efd; letter-spacing: -1px; }

        /* Estilo para los datos de contacto sin depender de emojis */
        .contact-info { font-size: 10px; color: #636e72; margin-top: 5px; }
        .contact-info span { color: #0d6efd; font-weight: bold; }

        .id-badge {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 12px 18px;
        }

        .document-title {
            padding: 0 50px;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .section {
            margin: 0 50px 25px 50px;
            border-top: 1px solid #edf2f7;
            padding-top: 15px;
        }

        .section-title {
            width: 120px;
            font-size: 9px;
            font-weight: bold;
            color: #0d6efd;
            text-transform: uppercase;
            vertical-align: top;
        }

        .label { font-size: 8px; color: #b2bec3; text-transform: uppercase; font-weight: bold; }
        .value { font-size: 12px; font-weight: bold; color: #2d3436; margin-bottom: 10px; }

        .prestacion-card {
            background-color: #f1f7ff;
            border-left: 4px solid #0d6efd;
            padding: 15px 20px;
            border-radius: 4px;
        }

        /* Footer fijo mejorado */
        .footer-fixed {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 160px; /* Altura fija para evitar traslape */
        }

        .qr-container {
            margin: 0 50px 20px 50px;
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #e9ecef;
        }

        .legal-footer {
            border-top: 1px solid #edf2f7;
            padding: 15px 50px;
            text-align: center;
            font-size: 9px;
            color: #b2bec3;
        }
    </style>
</head>
<body>

<div class="top-bar"></div>

<div class="header">
    <table width="100%">
        <tr>
            <td width="60%">
                <div class="logo">Doctor911</div>
                <div class="contact-info">
                    doctor911.cl &bull; contacto@doctor911.cl<br>
                    <span>TEL:</span> +56 9 1234 5678
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
                                <div class="value" style="margin:0; color: #0d6efd;">#{{ strtoupper(substr($order->id, 0, 8)) }}</div>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="document-title">Orden Médica</div>

<div class="section">
    <table width="100%">
        <tr>
            <td class="section-title">Médico Emisor</td>
            <td>
                <div class="value" style="font-size: 15px; margin-bottom: 4px;">Dr. {{ $order->doctor->user->name }}</div>
                <div style="color: #636e72; font-size: 10px;">
                    RUT: {{ $order->doctor->rut }} | Registro SIS: {{ $order->doctor->rnpi_number }}<br>
                    <span style="color: #0d6efd; font-weight: bold;">
                        {{ strtoupper($order->doctor->specialties->pluck('name')->implode(' / ')) }}
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
                        <td width="50%"><div class="label">Nombre</div><div class="value">{{ strtoupper($order->patient->full_name) }}</div></td>
                        <td width="30%"><div class="label">RUT</div><div class="value">{{ $order->patient->rut }}</div></td>
                        <td><div class="label">Edad</div><div class="value">{{ $order->patient_age_at_order ?? $order->patient->age }} años</div></td>
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
                    <div class="value" style="font-size: 15px; color: #0d6efd; margin-bottom: 5px;">
                        {{ $order->display_name }} {{-- <-- Usando el Accessor que definimos --}}
                    </div>
                    <div style="color: #636e72; font-style: italic; font-size: 9px;">
                        Documento válido para laboratorios y centros de salud en todo Chile.
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
                <td width="70">
                    <img src="data:image/png;base64,{{ $qrCode }}" style="width: 70px; height: 70px;">
                </td>
                <td style="padding-left: 15px; vertical-align: middle;">
                    <div style="color: #0d6efd; font-weight: bold; font-size: 10px; margin-bottom: 3px;">VERIFICACIÓN DIGITAL</div>
                    <div style="color: #636e72; font-size: 9px; line-height: 1.2;">
                        Escanee el código para confirmar la autenticidad del documento o ingrese el código
                        <strong>{{ $order->verification_code }}</strong> en doctor911.cl/validar.
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
