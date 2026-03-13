<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Helvetica', sans-serif;
            color: #1e293b;
            font-size: 11px;
            margin: 0;
            line-height: 1.5;
            background-color: #ffffff;
        }

        /* Colores */
        .text-blue { color: #0056b3; }
        .bg-navy { background-color: #0f172a; } /* Azul muy oscuro para contraste */

        /* Marca de Agua / Fondo sutil */
        .watermark {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 400px;
            color: #f8fafc;
            z-index: -1;
            font-weight: bold;
        }

        /* Header Premium */
        .header-top {
            background: linear-gradient(90deg, #0056b3 0%, #003d7a 100%);
            padding: 30px 50px;
            color: white;
            position: relative;
        }

        .header-logo { font-size: 28px; font-weight: 800; letter-spacing: -1.5px; margin: 0; }
        .header-sub { font-size: 10px; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px; }

        .qr-header {
            position: absolute;
            right: 50px;
            top: 25px;
            background: white;
            padding: 8px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* Contenido */
        .main-content { padding: 40px 50px; }

        .document-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            border-bottom: 3px solid #0056b3;
            display: inline-block;
            margin-bottom: 30px;
            padding-bottom: 5px;
        }

        /* Tarjeta de Información del Paciente */
        .info-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }

        .label { font-size: 9px; text-transform: uppercase; color: #64748b; font-weight: 700; margin-bottom: 3px; }
        .value { font-size: 13px; font-weight: 700; color: #1e293b; }

        /* Área de Prescripción */
        .prescription-area {
            margin-top: 20px;
            min-height: 350px;
        }

        .rp-icon {
            font-size: 35px;
            font-weight: 800;
            color: #0056b3;
            margin-bottom: 15px;
            font-style: italic;
        }

        .indications-text {
            font-size: 16px;
            color: #334155;
            padding-left: 10px;
            border-left: 3px solid #e2e8f0;
        }

        /* Firma y Footer */
        .footer-table { width: 100%; margin-top: 40px; }

        .signature-box {
            text-align: center;
            width: 250px;
        }

        .signature-img {
            max-width: 160px;
            margin-bottom: 10px;
            filter: contrast(1.2);
        }

        .doc-name { font-size: 13px; font-weight: 800; color: #0f172a; margin: 0; }
        .doc-meta { font-size: 9px; color: #64748b; margin-top: 3px; }

        .verification-badge {
            background: #f1f5f9;
            padding: 15px;
            border-radius: 8px;
            font-size: 9px;
            color: #475569;
            border: 1px dashed #cbd5e0;
        }

        .bottom-strip {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 6px;
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="watermark">Rp</div>

    <div class="header-top">
        <div class="qr-header">
            <img src="data:image/png;base64,{{ $qrCode }}" width="65">
        </div>
        <p class="header-logo">DOCTOR 911</p>
        <p class="header-sub">Servicios Médicos Digitales • Red MedOrder</p>
    </div>

    <div class="main-content">
        <div class="document-title">ORDEN MÉDICA ELECTRÓNICA</div>

        <div class="info-card">
            <table width="100%">
                <tr>
                    <td width="55%">
                        <div class="label">Paciente</div>
                        <div class="value">{{ strtoupper($order->patient->full_name) }}</div>
                    </td>
                    <td width="25%">
                        <div class="label">R.U.T</div>
                        <div class="value">{{ $order->patient->rut }}</div>
                    </td>
                    <td width="20%">
                        <div class="label">Fecha</div>
                        <div class="value">{{ $order->created_at->format('d/m/Y') }}</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="padding-top: 15px;">
                        <span class="label">ID de Documento:</span>
                        <span style="font-family: monospace; font-weight: bold;">{{ strtoupper(substr($order->id, 0, 12)) }}</span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="prescription-area">
            <div class="rp-icon">Rp.</div>
            <div class="indications-text">
                {{-- Aquí va el contenido que arreglaremos --}}
                <strong style="font-size: 18px;">{{ $order->clinical_context }}</strong>
                <p style="color: #64748b; font-size: 11px; margin-top: 20px;">
                    * Prestación autorizada para ser realizada en cualquier centro de salud en convenio.
                </p>
            </div>
        </div>

        <table class="footer-table">
            <tr>
                <td width="50%" style="vertical-align: bottom;">
                    <div class="verification-badge">
                        <strong>VERIFICACIÓN DE SEGURIDAD</strong><br>
                        Este documento cuenta con firma electrónica avanzada. Puede validar su integridad en www.doctor911.cl con el código de seguimiento indicado arriba.
                    </div>
                </td>
                <td width="50%" align="right">
                    <div class="signature-box">
                        @if($order->doctor->signature_path)
                            <img src="{{ public_path('storage/' . $order->doctor->signature_path) }}" class="signature-img">
                        @endif
                        <div style="border-top: 1px solid #e2e8f0; padding-top: 8px;">
                            <p class="doc-name">DR. {{ strtoupper($order->doctor->user->name) }}</p>
                            <p class="doc-meta">
                                RUT: {{ $order->doctor->rut }} | Reg. SIS: {{ $order->doctor->rnpi_number }}<br>
                                @foreach($order->doctor->specialties as $specialty)
                                    {{ strtoupper($specialty->name) }}{{ !$loop->last ? ' / ' : '' }}
                                @endforeach
                            </p>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="bottom-strip"></div>
</body>
</html>
