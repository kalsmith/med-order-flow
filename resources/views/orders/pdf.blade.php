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
            color: #1f2937;
            line-height: 1.5;
        }

        /* BARRA SUPERIOR E IDENTIDAD */
        .top-bar { height: 12px; background: #6366f1; }

        .header { padding: 40px 50px 20px 50px; }
        .header-table { width: 100%; }
        .logo { font-size: 28px; font-weight: 800; color: #1e1b4b; letter-spacing: -1px; }

        /* CONTACTO DIGITAL */
        .contact-info { font-size: 10px; color: #64748b; margin-top: 5px; }
        .contact-info span { margin-right: 15px; }

        .header-right { text-align: right; }
        .meta-tag {
            display: inline-block;
            background: #f1f5f9;
            padding: 8px 12px;
            border-radius: 6px;
            text-align: left;
            border: 1px solid #e2e8f0;
        }

        /* CUERPO DEL DOCUMENTO */
        .title { margin: 10px 50px 25px 50px; font-size: 22px; font-weight: 800; color: #1e1b4b; text-transform: uppercase; }

        .section { margin: 0 50px 30px 50px; }
        .section-header {
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .section-title {
            font-size: 10px;
            color: #6366f1;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 0.8px;
        }

        /* GRILLA DE DATOS */
        .data-table { width: 100%; border-collapse: collapse; }
        .label { font-size: 9px; color: #94a3b8; text-transform: uppercase; font-weight: 700; }
        .value { font-size: 13px; font-weight: 600; color: #111827; }

        /* PRESTACIONES RESALTADAS */
        .exam-card {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #6366f1;
        }
        .exam-name { font-size: 16px; font-weight: 800; color: #1e1b4b; }

        /* FIRMA ELECTRÓNICA QR */
        .validation-area {
            margin: 50px 50px 0 50px;
            padding: 25px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }
        .qr-box { width: 90px; text-align: center; }
        .validation-text { padding-left: 20px; vertical-align: middle; }
        .cert-title { font-weight: 800; font-size: 11px; color: #1e1b4b; margin-bottom: 5px; }

        /* FOOTER DINÁMICO (Al fondo) */
        .footer-wrapper {
            position: absolute;
            bottom: 0;
            width: 100%;
        }
        .footer-line { border-top: 1px solid #f1f5f9; margin: 0 50px; }
        .footer-content {
            padding: 20px 50px 30px 50px;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
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
                    <span>🌐 www.doctor911.cl</span>
                    <span>📧 contacto@doctor911.cl</span>
                    <span>📞 +56 9 1234 5678</span>
                </div>
            </td>
            <td class="header-right">
                <div class="meta-tag">
                    <span class="label">Emisión:</span> <span class="value">{{ $order->created_at->format('d/m/Y') }}</span><br>
                    <span class="label">ID Orden:</span> <span class="value" style="font-family: monospace;">{{ strtoupper(substr($order->id,0,12)) }}</span>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="title">Orden médica electrónica</div>

<div class="section">
    <div class="section-header"><span class="section-title">Datos del Paciente</span></div>
    <table class="data-table">
        <tr>
            <td width="50%">
                <div class="label">Nombre del Paciente</div>
                <div class="value">{{ strtoupper($order->patient->full_name) }}</div>
            </td>
            <td width="25%">
                <div class="label">R.U.T</div>
                <div class="value">{{ $order->patient->rut }}</div>
            </td>
            <td width="25%">
                <div class="label">Edad</div>
                <div class="value">{{ $order->patient->age }} Años</div>
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-header"><span class="section-title">Exámenes Solicitados</span></div>
    <div class="exam-card">
        <div class="exam-name">{{ $order->clinical_context }}</div>
        <div style="margin-top: 8px; color: #64748b; font-style: italic; font-size: 10px;">
            Documento válido para ser presentado en laboratorios y centros de salud en convenio a nivel nacional.
        </div>
    </div>
</div>

<div class="section">
    <div class="section-header"><span class="section-title">Médico Emisor</span></div>
    <table width="100%">
        <tr>
            <td>
                <div class="value" style="font-size: 14px;">Dr. {{ $order->doctor->user->name }}</div>
                <div style="color: #64748b; margin-top: 4px;">
                    RUT: {{ $order->doctor->rut }} | Reg. SIS: {{ $order->doctor->rnpi_number }}<br>
                    Especialidad:
                    @foreach($order->doctor->specialties as $specialty)
                        {{ $specialty->name }}{{ !$loop->last ? ' / ' : '' }}
                    @endforeach
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="validation-area">
    <table width="100%">
        <tr>
            <td class="qr-box">
                <img src="data:image/png;base64,{{ $qrCode }}" width="85">
            </td>
            <td class="validation-text">
                <div class="cert-title">FIRMA ELECTRÓNICA AVANZADA</div>
                <div style="color: #64748b; font-size: 9px; line-height: 1.4;">
                    Este documento ha sido emitido y firmado electrónicamente mediante protocolos de seguridad digital.
                    La integridad y validez legal de esta orden puede ser verificada escaneando el código QR
                    o ingresando el ID de seguimiento en <strong>www.doctor911.cl/validar</strong>.
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="footer-wrapper">
    <div class="footer-line"></div>
    <div class="footer-content">
        <strong>CODE TECH DIGITAL SPA</strong> · RUT 77.736.856-7 · Registro Superintendencia de Salud<br>
        Documento generado automáticamente por el sistema MedOrderFlow v2.0
    </div>
</div>

</body>
</html>
