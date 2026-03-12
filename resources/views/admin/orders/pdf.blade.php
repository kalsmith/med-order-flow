<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.4; }
        .header { border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-bottom: 20px; }
        .logo-container { width: 100%; }
        .qr-code { float: right; margin-top: -10px; }
        .info-box { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .patient-title { font-weight: bold; color: #0056b3; text-transform: uppercase; font-size: 12px; }
        .content { min-height: 300px; padding: 10px; }
        .footer { border-top: 1px solid #ccc; padding-top: 15px; margin-top: 50px; text-align: center; }
        .signature { text-align: center; width: 250px; margin-left: auto; }
        .legal-text { font-size: 9px; color: #666; margin-top: 20px; font-style: italic; }
    </style>
</head>
<body>
    <div class="header">
        <div class="qr-code">
            {{-- Aquí va tu lógica de QR --}}
            <img src="data:image/png;base64,{{ $qrCode }}" width="100">
        </div>
        <div class="logo-container">
            <h1 style="margin: 0; color: #0056b3;">DOCTOR 911</h1> {{-- Basado en source 1 --}}
            <small>Orden de Examen Médica</small>
        </div>
    </div>

    <div class="info-box">
        <table width="100%">
            <tr>
                <td width="50%">
                    <span class="patient-title">Paciente</span><br>
                    <strong>Nombre:</strong> {{ $order->patient->full_name }} [cite: 4, 20]<br>
                    <strong>RUT:</strong> {{ $order->patient->rut }} [cite: 4, 21]<br>
                    <strong>Edad:</strong> {{ $order->patient->age }} años [cite: 4, 22]
                </td>
                <td width="50%" style="vertical-align: top; text-align: right;">
                    <strong>Fecha:</strong> {{ $order->created_at->format('d/m/Y') }} [cite: 10, 19]
                </td>
            </tr>
        </table>
    </div>

    <div class="content">
        <p><strong>Rp:</strong> [cite: 5]</p>
        <div style="margin-left: 20px; font-size: 16px;">
            {!! nl2br(e($order->clinical_context)) !!}
        </div>
    </div>

    <div class="footer">
        <div class="signature">
            <img src="{{ public_path('storage/' . $order->doctor->signature_path) }}" width="150"><br>
            <strong>Dr. {{ $order->doctor->user->name }}</strong> [cite: 2, 12, 26]<br>
            R.U.T: {{ $order->doctor->rut }} [cite: 2, 12, 27]<br>
            <small>Médico Urgenciólogo / S.I.S: {{ $order->doctor->sis_number }}</small> [cite: 2, 27]
        </div>

        <p class="legal-text">
            Esta Orden Médica no debe ser sustituida o manipulada <br>
            Documento firmado electrónicamente. Valide su autenticidad escaneando el código QR.
        </p>
    </div>
</body>
</html>
