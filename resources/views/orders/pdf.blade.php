<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica', Arial, sans-serif; color: #333; font-size: 12px; margin: 0; }

        /* Header Estilo Mateo Alonso */
        .header { width: 100%; margin-bottom: 30px; }
        .logo-txt { color: #0056b3; font-size: 28px; font-weight: bold; margin: 0; }
        .logo-sub { color: #666; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; }

        .qr-box { float: right; text-align: center; }
        .qr-box small { display: block; font-size: 8px; color: #999; margin-top: 5px; }

        /* Bloque Paciente */
        .info-container { background: #fdfdfd; border: 1px solid #eee; padding: 15px; border-radius: 8px; margin-bottom: 30px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .label { color: #0056b3; font-weight: bold; font-size: 10px; text-transform: uppercase; }
        .value { font-size: 13px; margin-bottom: 5px; display: block; }

        /* Cuerpo de la Orden */
        .content { min-height: 400px; padding: 20px 10px; border-left: 3px solid #0056b3; margin-left: 10px; }
        .rp-label { font-size: 20px; font-weight: bold; color: #0056b3; margin-bottom: 15px; }
        .med-indications { font-size: 16px; line-height: 1.6; }

        /* Pie de página y Firma */
        .footer { position: absolute; bottom: 0; width: 100%; border-top: 1px solid #eee; padding-top: 20px; }
        .signature-wrapper { float: right; text-align: center; width: 280px; }
        .signature-img { max-width: 200px; height: auto; margin-bottom: 5px; }
        .doc-name { font-size: 14px; font-weight: bold; margin: 0; }
        .doc-details { font-size: 10px; color: #555; line-height: 1.2; }

        .legal { font-size: 9px; color: #999; margin-top: 40px; font-style: italic; clear: both; }
    </style>
</head>
<body>
    <div class="header">
        <div class="qr-box">
            <img src="data:image/png;base64,{{ $qrCode }}" width="90">
            <small>VALIDAR ORDEN</small>
        </div>
        <div class="logo-area">
            <p class="logo-txt">DOCTOR 911</p>
            <p class="logo-sub">Servicios Médicos Digitales</p>
        </div>
    </div>

    <div class="info-container">
        <table class="info-table">
            <tr>
                <td width="70%">
                    <span class="label">Paciente</span>
                    <span class="value">{{ $order->patient->full_name }}</span>

                    <span class="label">RUT</span>
                    <span class="value">{{ $order->patient->rut }}</span>
                </td>
                <td width="30%" style="vertical-align: top; text-align: right;">
                    <span class="label">Fecha de Emisión</span>
                    <span class="value">{{ $order->created_at->format('d/m/Y') }}</span>

                    <span class="label">Edad</span>
                    <span class="value">{{ $order->patient->age }} años</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="content">
        <div class="rp-label">Rp:</div>
        <div class="med-indications">
            {!! nl2br(e($order->clinical_context)) !!}
        </div>
    </div>

    <div class="footer">
        <div class="signature-wrapper">
            @if($order->doctor->signature_path)
                <img src="{{ public_path('storage/' . $order->doctor->signature_path) }}" class="signature-img">
            @else
                <div style="height: 80px;"></div> {{-- Espacio si no hay firma --}}
            @endif

            <p class="doc-name">Dr. {{ $order->doctor->user->name }}</p>
            <div class="doc-details">
                R.U.T: {{ $order->doctor->rut }}<br>
                {{-- Extraemos las especialidades dinámicamente --}}
                @foreach($order->doctor->specialties as $specialty)
                    {{ $specialty->name }}{{ !$loop->last ? ' / ' : '' }}
                @endforeach
                <br>
                Registro S.I.S: {{ $order->doctor->rnpi_number ?? 'Pendiente' }}
            </div>
        </div>

        <p class="legal">
            Esta Orden Médica no debe ser sustituida o manipulada. <br>
            Documento firmado electrónicamente. Validez legal según Norma Técnica Chilena.
        </p>
    </div>
</body>
</html>
