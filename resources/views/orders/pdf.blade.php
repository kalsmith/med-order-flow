<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        /* Configuraciones de página para dompdf */
        @page { margin: 0; }
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            color: #1a202c;
            font-size: 11px;
            margin: 0;
            background-color: #ffffff;
        }

        /* Decoración Superior */
        .top-bar { height: 8px; background: #0056b3; width: 100%; }

        .container { padding: 40px; }

        /* Header */
        .header { width: 100%; margin-bottom: 40px; }
        .logo-txt { color: #0056b3; font-size: 24px; font-weight: 800; margin: 0; letter-spacing: -1px; }
        .logo-sub { color: #718096; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; margin-top: 2px; }

        .qr-box { float: right; text-align: right; }
        .qr-box img { margin-bottom: 5px; }
        .id-label { font-size: 9px; color: #a0aec0; }
        .id-value { font-family: monospace; font-size: 11px; color: #2d3748; font-weight: bold; }

        /* Estructura de Secciones (Look PaperMed) */
        .section-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .section-title-cell {
            width: 120px;
            background-color: #f7fafc;
            border-right: 2px solid #edf2f7;
            padding: 15px 10px;
            vertical-align: top;
            text-align: right;
        }
        .section-title {
            color: #4a5568;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section-content { padding: 15px 20px; vertical-align: top; }

        /* Detalles de contenido */
        .data-row { margin-bottom: 5px; }
        .data-label { color: #718096; font-weight: bold; width: 60px; display: inline-block; }
        .med-list { font-size: 14px; font-weight: bold; color: #2d3748; line-height: 1.5; }

        /* Firma */
        .footer-area { margin-top: 50px; border-top: 1px solid #edf2f7; padding-top: 30px; }
        .signature-col { width: 50%; float: right; text-align: center; }
        .signature-img { max-width: 180px; height: auto; margin-bottom: 5px; }
        .doc-info { font-size: 10px; color: #4a5568; line-height: 1.3; }

        .bottom-legal {
            position: absolute;
            bottom: 30px;
            width: 100%;
            padding: 0 40px;
            font-size: 8px;
            color: #a0aec0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="top-bar"></div>

    <div class="container">
        <div class="header">
            <div class="qr-box">
                <img src="data:image/png;base64,{{ $qrCode }}" width="75">
                <div class="id-label">ID DE ORDEN</div>
                <div class="id-value">{{ strtoupper($order->id_short ?? substr($order->id, 0, 8)) }}</div>
            </div>
            <div class="logo-area">
                <p class="logo-txt">DOCTOR 911</p>
                <p class="logo-sub">Servicios Médicos Digitales • Telemedicina</p>
            </div>
        </div>

        <h1 style="font-size: 18px; color: #2d3748; margin-bottom: 30px;">Orden Médica</h1>

        <table class="section-table">
            <tr>
                <td class="section-title-cell"><span class="section-title">Paciente</span></td>
                <td class="section-content">
                    <div style="font-size: 14px; font-weight: bold; margin-bottom: 5px;">{{ $order->patient->full_name }}</div>
                    <table width="100%">
                        <tr>
                            <td width="50%">
                                <span class="data-label">RUT:</span> {{ $order->patient->rut }}<br>
                                <span class="data-label">EDAD:</span> {{ $order->patient->age }} años
                            </td>
                            <td width="50%" style="text-align: right;">
                                <span class="data-label">FECHA:</span> {{ $order->created_at->format('d/m/Y') }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="section-table">
            <tr>
                <td class="section-title-cell"><span class="section-title">Indicaciones</span></td>
                <td class="section-content">
                    <div class="med-list">
                        {{-- Aquí es donde arreglaremos el bug del contenido después --}}
                        {!! nl2br(e($order->clinical_context)) !!}
                    </div>
                    <div style="margin-top: 15px; color: #718096; font-style: italic; font-size: 10px;">
                        Nota: Validez de 30 días desde la fecha de emisión.
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer-area">
            <div class="signature-col">
                @if($order->doctor->signature_path)
                    <img src="{{ public_path('storage/' . $order->doctor->signature_path) }}" class="signature-img">
                @else
                    <div style="height: 80px; border-bottom: 1px dashed #cbd5e0; margin-bottom: 10px;"></div>
                @endif
                <div class="doc-info">
                    <strong>Dr. {{ $order->doctor->user->name }}</strong><br>
                    R.U.T: {{ $order->doctor->rut }}<br>
                    @foreach($order->doctor->specialties as $specialty)
                        {{ $specialty->name }}{{ !$loop->last ? ' / ' : '' }}
                    @endforeach<br>
                    Registro S.I.S: {{ $order->doctor->rnpi_number ?? '123456' }}
                </div>
            </div>

            <div style="float: left; width: 45%; margin-top: 20px;">
                <div style="border: 1px solid #edf2f7; padding: 10px; border-radius: 5px;">
                    <small style="color: #a0aec0; display: block; margin-bottom: 5px;">VALIDACIÓN</small>
                    <p style="font-size: 9px; margin: 0; color: #718096;">
                        Escanee el código QR para verificar la autenticidad de este documento en nuestro portal oficial.
                    </p>
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>

    <div class="bottom-legal">
        Este documento es una receta médica electrónica generada bajo los estándares de la Ley 21.242. <br>
        Doctor 911 Chile - www.doctor911.cl
    </div>
</body>
</html>
