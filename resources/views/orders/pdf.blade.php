<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        /* REGISTRO DE FUENTE ROBOTO */
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

        @page { margin: 0; }

        body {
            font-family: 'Roboto', sans-serif; /* ¡Adiós Helvetica! */
            margin: 0;
            font-size: 11px;
            color: #212529;
            line-height: 1.5;
        }

        .top-bar { height: 8px; background-color: #0d6efd; }
        .header { padding: 40px 50px 20px 50px; }
        .logo { font-size: 30px; font-weight: 900; color: #0d6efd; letter-spacing: -1.5px; }
        .contact-info { font-size: 10px; color: #6c757d; margin-top: 4px; }

        .header-right { text-align: right; }

        .id-badge {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            text-align: left;
            width: 100%;
            box-sizing: border-box;
        }

        .document-title {
            margin: 15px 50px 35px 50px;
            font-size: 24px;
            font-weight: 900;
            color: #212529;
            letter-spacing: -0.5px;
        }

        /* SECCIONES CON MÁS AIRE */
        .section-container {
            margin: 0 50px 30px 50px; /* Más espacio entre bloques */
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }

        .section-table { width: 100%; border-collapse: collapse; }
        .sidebar {
            width: 120px;
            vertical-align: top;
            font-size: 10px;
            color: #0d6efd;
            text-transform: uppercase;
            font-weight: 900; /* Roboto Black para títulos de sección */
            letter-spacing: 0.5px;
        }
        .content { padding-left: 20px; }

        .label { font-size: 8px; color: #6c757d; text-transform: uppercase; font-weight: 700; }
        .value { font-size: 13px; font-weight: 600; color: #212529; margin-bottom: 12px; }

        .exam-box {
            background-color: #f8f9fa;
            padding: 22px;
            border-radius: 8px;
            border-left: 5px solid #0d6efd;
        }
        .exam-name { font-size: 17px; font-weight: 900; color: #0d6efd; }

        .footer-wrapper {
            position: absolute;
            bottom: 0;
            width: 100%;
        }

        .verification-block {
            margin: 0 50px 30px 50px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }

        .qr-small { width: 75px; vertical-align: middle; }
        .verification-text { padding-left: 20px; vertical-align: middle; color: #495057; font-size: 10px; }
        .verification-title { font-weight: 900; color: #0d6efd; margin-bottom: 5px; font-size: 11px; }

        .final-footer {
            border-top: 1px solid #dee2e6;
            padding: 20px 50px;
            text-align: center;
            font-size: 9px;
            color: #adb5bd;
            font-weight: 700;
        }
    </style>
</head>
<body>

<div class="top-bar"></div>

<div class="header">
    <table class="header-table" width="100%">
        <tr>
            <td width="50%" style="vertical-align: top;">
                <div class="logo">Doctor911</div>
                <div class="contact-info">doctor911.cl &nbsp;•&nbsp; contacto@doctor911.cl</div>
                <div class="contact-info">📞 +56 9 1234 5678</div>
            </td>
            <td width="50%" class="header-right" style="vertical-align: top;">
                <div class="id-badge">
                    <table width="100%">
                        <tr>
                            <td>
                                <div class="label">Fecha Emisión</div>
                                <div class="value" style="margin-bottom:0;">{{ $order->created_at->format('d/m/Y') }}</div>
                            </td>
                            <td style="text-align: right;">
                                <div class="label">ID Seguimiento</div>
                                <div class="value" style="color:#0d6efd; font-family: monospace; margin-bottom:0;">{{ strtoupper(substr($order->id,0,12)) }}</div>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="document-title">Orden médica</div>

<div class="section-container">
    <table class="section-table">
        <tr>
            <td class="sidebar">Médico Emisor</td>
            <td class="content">
                <div class="value" style="font-size: 15px; margin-bottom: 4px;">Dr. {{ $order->doctor->user->name }}</div>
                <div style="color: #6c757d;">
                    RUT: {{ $order->doctor->rut }} | Reg. SIS: {{ $order->doctor->rnpi_number }}<br>
                    @foreach($order->doctor->specialties as $specialty)
                        {{ strtoupper($specialty->name) }}{{ !$loop->last ? ' / ' : '' }}
                    @endforeach
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="section-container">
    <table class="section-table">
        <tr>
            <td class="sidebar">Paciente</td>
            <td class="content">
                <table width="100%">
                    <tr>
                        <td width="50%"><div class="label">Nombre Completo</div><div class="value">{{ strtoupper($order->patient->full_name) }}</div></td>
                        <td><div class="label">RUT</div><div class="value">{{ $order->patient->rut }}</div></td>
                        <td><div class="label">Edad</div><div class="value">{{ $order->patient->age }} Años</div></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<div class="section-container">
    <table class="section-table">
        <tr>
            <td class="sidebar">Prestaciones</td>
            <td class="content">
                <div class="exam-box">
                    <div class="exam-name">{{ $order->clinical_context }}</div>
                    <div style="color: #6c757d; font-size: 10px; margin-top: 10px; font-style: italic;">
                        Documento válido para ser presentado en cualquier centro de salud o laboratorio en convenio.
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="footer-wrapper">
    <div class="verification-block">
        <table width="100%">
            <tr>
                <td width="75"><img src="data:image/png;base64,{{ $qrCode }}" class="qr-small"></td>
                <td class="verification-text">
                    <div class="verification-title">VERIFICACIÓN ELECTRÓNICA DE SEGURIDAD</div>
                    Este documento es una orden médica electrónica válida en todo el territorio nacional.
                    Para verificar su autenticidad, escanee el código QR o ingrese el ID del documento en <strong>doctor911.cl/validar</strong>.
                </td>
            </tr>
        </table>
    </div>

    <div class="final-footer">
        CODE TECH DIGITAL SPA · Doctor911 · RUT 77.736.856-7
    </div>
</div>

</body>
</html>
