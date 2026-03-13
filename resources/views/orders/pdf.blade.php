<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            margin: 0;
            font-size: 11px;
            color: #212529;
            line-height: 1.4;
        }

        /* IDENTIDAD VISUAL BOOTSTRAP */
        .top-bar { height: 8px; background-color: #0d6efd; }
        .header { padding: 35px 50px 15px 50px; }
        .logo { font-size: 28px; font-weight: 800; color: #0d6efd; letter-spacing: -1px; }
        .contact-info { font-size: 10px; color: #6c757d; margin-top: 4px; }

        .header-right { text-align: right; }
        .id-badge {
            display: inline-block;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            text-align: left;
        }

        .document-title { margin: 10px 50px 20px 50px; font-size: 20px; font-weight: 700; color: #212529; }

        /* SECCIONES TIPO PAPERMED */
        .section-container {
            margin: 0 50px 15px 50px;
            border-top: 1px solid #dee2e6;
            padding-top: 12px;
        }
        .section-table { width: 100%; border-collapse: collapse; }
        .sidebar { width: 110px; vertical-align: top; font-size: 10px; color: #0d6efd; text-transform: uppercase; font-weight: 800; }
        .content { padding-left: 15px; }

        .label { font-size: 8px; color: #6c757d; text-transform: uppercase; font-weight: 700; }
        .value { font-size: 12px; font-weight: 600; color: #212529; margin-bottom: 8px; }

        /* EXAMEN RESALTADO */
        .exam-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #0d6efd;
        }
        .exam-name { font-size: 15px; font-weight: 800; color: #0d6efd; }

        /* BLOQUE DE VERIFICACIÓN SOLICITADO */
        .footer-wrapper {
            position: absolute;
            bottom: 0;
            width: 100%;
        }

        .verification-block {
            margin: 0 50px 20px 50px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #e9ecef;
        }

        .qr-small { width: 70px; vertical-align: middle; }
        .verification-text { padding-left: 15px; vertical-align: middle; color: #495057; font-size: 10px; }
        .verification-title { font-weight: 800; color: #0d6efd; margin-bottom: 3px; font-size: 11px; }

        .final-footer {
            border-top: 1px solid #dee2e6;
            padding: 15px 50px;
            text-align: center;
            font-size: 9px;
            color: #adb5bd;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="top-bar"></div>

<div class="header">
    <table class="header-table" width="100%">
        <tr>
            <td>
                <div class="logo">Doctor911</div>
                <div class="contact-info">doctor911.cl &nbsp;•&nbsp; contacto@doctor911.cl &nbsp;•&nbsp; +56 9 1234 5678</div>
            </td>
            <td class="header-right">
                <div class="id-badge">
                    <div class="label">Fecha Emisión</div>
                    <div class="value">{{ $order->created_at->format('d/m/Y') }}</div>
                    <div class="label">ID Seguimiento</div>
                    <div class="value" style="color:#0d6efd; font-family: monospace;">{{ strtoupper(substr($order->id,0,12)) }}</div>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="document-title">Orden médica</div>

<div class="section-container">
    <table class="section-table">
        <tr>
            <td class="sidebar">Paciente</td>
            <td class="content">
                <table width="100%">
                    <tr>
                        <td width="50%"><div class="label">Nombre</div><div class="value">{{ strtoupper($order->patient->full_name) }}</div></td>
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
                    <div style="color: #6c757d; font-size: 10px; margin-top: 5px; font-style: italic;">
                        Válido para ser presentado en cualquier centro de salud en convenio.
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="section-container">
    <table class="section-table">
        <tr>
            <td class="sidebar">Médico</td>
            <td class="content">
                <div class="value" style="font-size: 14px; margin-bottom: 2px;">Dr. {{ $order->doctor->user->name }}</div>
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

<div class="footer-wrapper">
    <div class="verification-block">
        <table width="100%">
            <tr>
                <td width="70"><img src="data:image/png;base64,{{ $qrCode }}" class="qr-small"></td>
                <td class="verification-text">
                    <div class="verification-title">VERIFICACIÓN ELECTRÓNICA DE SEGURIDAD</div>
                    Este documento es una orden médica electrónica válida en todo el territorio nacional.
                    Para verificar su autenticidad y firma electrónica, escanee el código QR o ingrese el ID del documento en <strong>doctor911.cl/validar</strong>.
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
