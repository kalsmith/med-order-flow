<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            font-size: 11px;
            color: #212529; /* Gris oscuro Bootstrap */
            line-height: 1.5;
        }

        /* BARRA SUPERIOR BOOTSTRAP BLUE */
        .top-bar {
            height: 8px;
            background-color: #0d6efd;
        }

        .header { padding: 40px 50px 20px 50px; }
        .header-table { width: 100%; }

        .logo {
            font-size: 32px;
            font-weight: 800;
            color: #0d6efd;
            letter-spacing: -1.5px;
            margin: 0;
        }

        .contact-info { font-size: 10px; color: #6c757d; margin-top: 5px; }

        .header-right { text-align: right; }
        .id-badge {
            display: inline-block;
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        /* TÍTULO PRINCIPAL */
        .document-title {
            margin: 10px 50px 25px 50px;
            font-size: 22px;
            font-weight: 700;
            color: #0d6efd;
        }

        /* ESTRUCTURA DE SECCIONES TIPO PAPERMED */
        .section-container {
            margin: 0 50px 20px 50px;
            border-top: 1px solid #dee2e6; /* El separador que faltaba */
            padding-top: 15px;
        }

        .section-table { width: 100%; border-collapse: collapse; }

        .sidebar {
            width: 120px;
            vertical-align: top;
        }

        .section-label {
            font-size: 10px;
            color: #0d6efd;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .content { padding-left: 20px; }

        /* DATOS DEL PACIENTE */
        .label { font-size: 9px; color: #6c757d; text-transform: uppercase; font-weight: 700; }
        .value { font-size: 13px; font-weight: 600; color: #212529; margin-bottom: 10px; }

        /* PRESTACIONES */
        .exam-display {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 5px solid #0d6efd;
        }
        .exam-title { font-size: 16px; font-weight: 800; color: #0d6efd; }
        .exam-note { color: #6c757d; font-size: 11px; margin-top: 5px; }

        /* VALIDACIÓN Y QR */
        .footer-box {
            margin: 40px 50px;
            padding: 20px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
        }

        .qr-img { border-radius: 4px; }
        .validation-text {
            font-size: 10px;
            color: #495057;
            padding-left: 20px;
        }

        /* FOOTER FINAL */
        .footer-bottom {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 20px 0;
            border-top: 1px solid #dee2e6;
            text-align: center;
        }
        .footer-brand { font-size: 10px; color: #adb5bd; font-weight: 600; }
    </style>
</head>
<body>

<div class="top-bar"></div>

<div class="header">
    <table class="header-table">
        <tr>
            <td>
                <div class="logo">Doctor911</div>
                <div class="contact-info">
                    www.doctor911.cl &nbsp;•&nbsp; contacto@doctor911.cl &nbsp;•&nbsp; +56 9 1234 5678
                </div>
            </td>
            <td class="header-right">
                <div class="id-badge">
                    <div class="label">Fecha de Emisión</div>
                    <div class="value">{{ $order->created_at->format('d/m/Y') }}</div>
                    <div class="label">ID Verificación</div>
                    <div class="value" style="font-family: monospace; color:#0d6efd">{{ strtoupper(substr($order->id,0,12)) }}</div>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="document-title">Orden Médica</div>

<div class="section-container">
    <table class="section-table">
        <tr>
            <td class="sidebar"><span class="section-label">Paciente</span></td>
            <td class="content">
                <table width="100%">
                    <tr>
                        <td width="50%">
                            <div class="label">Nombre</div>
                            <div class="value" style="font-size: 15px;">{{ strtoupper($order->patient->full_name) }}</div>
                        </td>
                        <td>
                            <div class="label">RUT</div>
                            <div class="value">{{ $order->patient->rut }}</div>
                        </td>
                        <td>
                            <div class="label">Edad</div>
                            <div class="value">{{ $order->patient->age }} Años</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<div class="section-container">
    <table class="section-table">
        <tr>
            <td class="sidebar"><span class="section-label">Prestaciones</span></td>
            <td class="content">
                <div class="exam-display">
                    <div class="exam-title">{{ $order->clinical_context }}</div>
                    <div class="exam-note">Prestación autorizada para su realización en cualquier centro de salud en convenio.</div>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="section-container">
    <table class="section-table">
        <tr>
            <td class="sidebar"><span class="section-label">Médico</span></td>
            <td class="content">
                <div class="value" style="font-size: 14px; margin-bottom: 2px;">Dr. {{ $order->doctor->user->name }}</div>
                <div style="color: #6c757d; font-weight: 500;">
                    RUT: {{ $order->doctor->rut }} | Reg. SIS: {{ $order->doctor->rnpi_number }}<br>
                    @foreach($order->doctor->specialties as $specialty)
                        {{ strtoupper($specialty->name) }}{{ !$loop->last ? ' / ' : '' }}
                    @endforeach
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="footer-box">
    <table width="100%">
        <tr>
            <td width="80"><img src="data:image/png;base64,{{ $qrCode }}" width="80" class="qr-img"></td>
            <td class="validation-text">
                <div style="font-weight: 800; font-size: 11px; color: #0d6efd; margin-bottom: 5px;">VERIFICACIÓN ELECTRÓNICA DE SEGURIDAD</div>
                Este documento es una orden médica electrónica válida en todo el territorio nacional.
                Para verificar su autenticidad y firma electrónica, escanee el código QR o ingrese
                el ID del documento en <strong>doctor911.cl/validar</strong>.
            </td>
        </tr>
    </table>
</div>

<div class="footer-bottom">
    <div class="footer-brand">
        CODE TECH DIGITAL SPA · Doctor911 · RUT 77.736.856-7
    </div>
</div>

</body>
</html>
