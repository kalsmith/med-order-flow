<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        /* Configuraciones de página */
        @page { margin: 0; }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; /* Simula el look de Roboto/Inter */
            margin: 0;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.4;
            -webkit-font-smoothing: antialiased;
        }

        /* BARRA SUPERIOR - Look moderno */
        .top-bar {
            height: 12px;
            background: linear-gradient(90deg, #6366f1 0%, #4f46e5 100%);
        }

        .header { padding: 40px 50px 10px 50px; }
        .header-table { width: 100%; }

        /* Logo con tipografía pesada tipo Tech */
        .logo {
            font-size: 30px;
            font-weight: 900;
            color: #111827;
            letter-spacing: -1.5px;
            margin: 0;
        }

        .contact-info { font-size: 10px; color: #94a3b8; margin-top: 8px; font-weight: 500; }
        .contact-info span { margin-right: 12px; }

        .header-right { text-align: right; }
        .meta-box {
            display: inline-block;
            background: #f8fafc;
            padding: 12px;
            border-radius: 8px;
            text-align: left;
            border: 1px solid #f1f5f9;
        }

        /* CUERPO */
        .title {
            margin: 15px 50px 30px 50px;
            font-size: 24px;
            font-weight: 900;
            color: #111827;
            letter-spacing: -0.5px;
        }

        .section { margin: 0 50px 35px 50px; }

        /* Look PaperMed: Sidebar de color sutil */
        .section-table { width: 100%; border-collapse: collapse; }
        .section-sidebar {
            width: 100px;
            vertical-align: top;
            border-right: 2px solid #f1f5f9;
            padding-top: 5px;
        }
        .section-title {
            font-size: 9px;
            color: #6366f1;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .section-content { padding-left: 30px; }

        /* DATOS */
        .label { font-size: 8px; color: #94a3b8; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; }
        .value { font-size: 13px; font-weight: 600; color: #111827; margin-bottom: 12px; }

        /* PRESTACIONES */
        .exam-name {
            font-size: 18px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 5px;
        }
        .exam-sub { color: #64748b; font-size: 11px; font-weight: 400; }

        /* VALIDACIÓN QR - Más elegante */
        .validation-area {
            margin: 30px 50px;
            padding: 20px;
            background: #fafafa;
            border-radius: 15px;
            border: 1px solid #f1f5f9;
        }

        /* FOOTER FIJO ABAJO */
        .footer-fixed {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 25px 0;
            background: #fcfcfc;
            border-top: 1px solid #f1f5f9;
        }
        .footer-text {
            text-align: center;
            font-size: 9px;
            color: #cbd5e1;
            font-weight: 500;
            letter-spacing: 0.3px;
        }
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
                    <span>doctor911.cl</span>
                    <span>contacto@doctor911.cl</span>
                    <span>+56 9 1234 5678</span>
                </div>
            </td>
            <td class="header-right">
                <div class="meta-box">
                    <div class="label">Fecha Emisión</div>
                    <div class="value">{{ $order->created_at->format('d/m/Y') }}</div>
                    <div class="label">ID Verificación</div>
                    <div class="value" style="color:#6366f1">{{ strtoupper(substr($order->id,0,12)) }}</div>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="title">Orden médica</div>

<div class="section">
    <table class="section-table">
        <tr>
            <td class="section-sidebar"><span class="section-title">Paciente</span></td>
            <td class="section-content">
                <table width="100%">
                    <tr>
                        <td width="60%">
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

<div class="section">
    <table class="section-table">
        <tr>
            <td class="section-sidebar"><span class="section-title">Prestaciones</span></td>
            <td class="section-content">
                <div class="exam-name">{{ $order->clinical_context }}</div>
                <div class="exam-sub">Prestación autorizada para libre elección en centros de salud.</div>
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <table class="section-table">
        <tr>
            <td class="section-sidebar"><span class="section-title">Emisor</span></td>
            <td class="section-content">
                <div class="value" style="font-size: 14px; margin-bottom: 4px;">Dr. {{ $order->doctor->user->name }}</div>
                <div style="color: #64748b; font-weight: 500;">
                    RUT: {{ $order->doctor->rut }} | Reg. SIS: {{ $order->doctor->rnpi_number }}<br>
                    @foreach($order->doctor->specialties as $specialty)
                        {{ strtoupper($specialty->name) }}{{ !$loop->last ? ' / ' : '' }}
                    @endforeach
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="validation-area">
    <table width="100%">
        <tr>
            <td width="80"><img src="data:image/png;base64,{{ $qrCode }}" width="80"></td>
            <td style="padding-left: 20px;">
                <div style="font-weight: 800; font-size: 11px; color: #111827; margin-bottom: 4px;">VERIFICACIÓN ELECTRÓNICA AVANZADA</div>
                <div style="color: #94a3b8; font-size: 9px; font-weight: 500;">
                    Este documento cuenta con validez legal según la normativa vigente de telemedicina.
                    Para validar, escanee el código o ingrese el ID en <strong>doctor911.cl/validar</strong>.
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="footer-fixed">
    <div class="footer-text">
        <strong>CODE TECH DIGITAL SPA</strong> · Doctor911 · RUT 77.736.856-7 · www.doctor911.cl
    </div>
</div>

</body>
</html>
