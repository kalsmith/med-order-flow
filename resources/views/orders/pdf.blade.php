<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; margin: 0; }
        .header { border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-bottom: 20px; }
        .qr-code { float: right; }
        .info-box { background: #f8f9fa; padding: 15px; border: 1px solid #eee; border-radius: 5px; margin-bottom: 20px; }
        .content { min-height: 350px; padding: 10px; font-size: 16px; }
        .footer { border-top: 1px solid #ccc; padding-top: 15px; margin-top: 30px; }
        .signature { text-align: center; width: 250px; float: right; }
        .legal-text { font-size: 9px; color: #666; margin-top: 20px; font-style: italic; clear: both; }
    </style>
</head>
<body>
    <div class="header">
        <div class="qr-code">
            {{-- El QR dinámico que genera tu servicio --}}
            <img src="data:image/png;base64,{{ $qrCode }}" width="100">
        </div>
        <div>
            <h1 style="margin: 0; color: #0056b3;">DOCTOR 911</h1>
            <p style="margin: 0;">Orden de Examen Médica</p>
        </div>
    </div>

    <div class="info-box">
        <table width="100%">
            <tr>
                <td width="60%">
                    <strong style="color: #0056b3;">PACIENTE</strong><br>
                    <strong>Nombre:</strong> {{ $order->patient->full_name }}<br>
                    <strong>RUT:</strong> {{ $order->patient->rut }}<br>
                    <strong>Edad:</strong> {{ $order->patient->age }} años
                </td>
                <td width="40%" style="vertical-align: top; text-align: right;">
                    <strong>Fecha:</strong> {{ $order->created_at->format('d/m/Y') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="content">
        <p><strong>Rp:</strong></p>
        <div style="margin-left: 20px;">
            {!! nl2br(e($order->clinical_context)) !!}
        </div>
    </div>

    <div class="footer">
        <div class="signature">
            {{-- Aquí se estampa la firma dinámica del doctor --}}
            @if($order->doctor->signature_path)
                <img src="{{ public_path('storage/' . $order->doctor->signature_path) }}" width="180"><br>
            @endif
            <strong>Dr. {{ $order->doctor->user->name }}</strong><br>
            R.U.T: {{ $order->doctor->rut }}<br>
            <small>Médico Urgenciólogo / S.I.S: {{ $order->doctor->sis_number }}</small>
        </div>

        <p class="legal-text">
            Esta Orden Médica no debe ser sustituida o manipulada.<br>
            Documento generado y firmado electrónicamente en med-order-flow.soltys.cl.
        </p>
    </div>
</body>
</html>
