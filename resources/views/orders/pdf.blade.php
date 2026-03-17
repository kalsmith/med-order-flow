<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        /* Optimización de fuentes */
        @font-face {
            font-family: 'Roboto';
            src: url("{{ public_path('fonts/Roboto-Regular.ttf') }}") format('truetype');
            font-weight: normal;
        }
        @font-face {
            font-family: 'Roboto';
            src: url("{{ public_path('fonts/Roboto-Bold.ttf') }}") format('truetype');
            font-weight: bold;
        }

        @page { margin: 0px; }

        body {
            font-family: 'Roboto', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0 0 180px 0;
            font-size: 11px;
            color: #2d3436;
            line-height: 1.4;
            background-color: #ffffff;
        }

        .top-bar { height: 6px; background-color: #0d6efd; width: 100%; }
        .header { padding: 40px 50px 25px 50px; }

        .logo-img { height: 45px; width: auto; display: block; margin-bottom: 5px; }

        .contact-info { font-size: 10px; color: #636e72; margin-top: 5px; }
        .contact-info span { color: #0d6efd; font-weight: bold; }

        .id-badge {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 12px 18px;
        }

        .document-title {
            padding: 0 50px;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
            color: #2d3436;
        }

        .section {
            margin: 0 50px 20px 50px;
            border-top: 1px solid #edf2f7;
            padding-top: 15px;
        }

        .section-title {
            width: 120px;
            font-size: 9px;
            font-weight: bold;
            color: #0d6efd;
            text-transform: uppercase;
            vertical-align: top;
        }

        .label { font-size: 8px; color: #b2bec3; text-transform: uppercase; font-weight: bold; }
        .value { font-size: 12px; font-weight: bold; color: #2d3436; margin-bottom: 10px; }

        .prestacion-card {
            background-color: #f1f7ff;
            border-left: 4px solid #0d6efd;
            padding: 15px 20px;
            border-radius: 4px;
        }

        .exam-item {
            margin-bottom: 5px;
            padding-bottom: 3px;
            border-bottom: 1px dotted #d1e1f5;
            display: block;
        }

        .exam-code {
            color: #0d6efd;
            font-weight: bold;
            font-size: 9px;
            margin-right: 5px;
        }

        .footer-fixed {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 160px;
        }

        .qr-container {
            margin: 0 50px 20px 50px;
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #e9ecef;
        }

        .legal-footer {
            border-top: 1px solid #edf2f7;
            padding: 15px 50px;
            text-align: center;
            font-size: 9px;
            color: #b2bec3;
        }

        .signature-container { margin-top: 10px; text-align: left; }
        .signature-img { max-height: 75px; max-width: 180px; display: block; }
    </style>
</head>
<body>

<div class="top-bar"></div>

<div class="header">
    <table width="100%">
        <tr>
            <td width="60%">
                {{-- Mantenemos tu URL que ya funcionaba para el logo --}}
                <img src="https://med-order-flow.soltys.cl/assets/logo/logo.png" class="logo-img">
                <div class="contact-info">
                    pidetuexamen.cl &bull; contacto@pidetuexamen.cl<br>
                    <span>TEL:</span> +56 9 1234 5678
                </div>
            </td>
            <td width="40%">
                <div class="id-badge">
                    <table width="100%">
                        <tr>
                            <td>
                                <div class="label">Emisión</div>
                                <div class="value" style="margin:0;">{{ $prescription->signed_at->format('d/m/Y') }}</div>
                            </td>
                            <td align="right">
                                <div class="label">N° Correlativo</div>
                                <div class="value" style="margin:0; color: #0d6efd;">#{{ $prescription->correlative_number }}</div>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="document-title">Orden Médica</div>

<div class="section">
    <table width="100%">
        <tr>
            <td class="section-title">Médico Emisor</td>
            <td>
                <div class="value" style="font-size: 15px; margin-bottom: 4px;">
                    Dr(a). {{ $prescription->doctor->user->name }}
                </div>
                <div style="color: #636e72; font-size: 10px;">
                    RUT: {{ $prescription->doctor->rut }} | Registro SIS: {{ $prescription->doctor->rnpi_number }}<br>
                    <span style="color: #0d6efd; font-weight: bold;">
                        {{ strtoupper($prescription->doctor->specialties->pluck('name')->join(' / ')) }}
                    </span>
                </div>

                <div style="background: #fdf6e3; border: 1px solid #eee; padding: 5px; margin-top: 10px; font-family: monospace; font-size: 8px;">
                    <strong>DEBUG INFO:</strong><br>
                    Path en DB: {{ $prescription->doctor->signature_path ?? 'NULO' }}<br>
                    ¿Llegó Base64?: {{ isset($signatureBase64) && $signatureBase64 ? 'SÍ ('.strlen($signatureBase64).' chars)' : 'NO' }}
                </div>
                <div class="signature-container">
                    @if(isset($signatureBase64) && $signatureBase64)
                        {{-- Renderizado mediante Base64 (Incrustado) --}}
                        <img src="{{ $signatureBase64 }}" class="signature-img">
                    @elseif($prescription->doctor->signature_path)
                        {{-- Fallback: Intento por URL si Base64 fallara --}}
                        @php $filename = basename($prescription->doctor->signature_path); @endphp
                        <img src="{{ route('public.signature.show', ['filename' => $filename]) }}" class="signature-img">
                    @else
                        {{-- Sin firma registrada --}}
                        <div style="margin-top: 10px; color: #b2bec3; font-style: italic; font-size: 8px;">
                            Documento firmado electrónicamente bajo Ley N° 19.799
                        </div>
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- Resto del documento se mantiene igual para asegurar que no se rompa nada --}}
<div class="section">
    <table width="100%">
        <tr>
            <td class="section-title">Paciente</td>
            <td>
                <table width="100%">
                    <tr>
                        <td width="55%"><div class="label">Nombre</div><div class="value">{{ strtoupper($order->patient->full_name) }}</div></td>
                        <td width="25%"><div class="label">RUT</div><div class="value">{{ $order->patient->rut }}</div></td>
                        <td><div class="label">Edad</div><div class="value">{{ $order->patient->age }} años</div></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <table width="100%">
        <tr>
            <td class="section-title">Análisis Solicitados</td>
            <td>
                <div class="prestacion-card">
                    <div class="value" style="font-size: 14px; color: #0d6efd; margin-bottom: 8px; border-bottom: 1px solid #cce0ff; padding-bottom: 5px;">
                        @if($order->type === 'custom')
                            ORDEN MÉDICA
                        @else
                            {{ $order->examType->name ?? 'EXAMEN ESTÁNDAR' }}
                        @endif
                    </div>

                    @if($order->type === 'custom')
                        <div style="font-size: 11px; font-weight: bold; white-space: pre-wrap; color: #2d3436;">{{ $prescription->clinical_context }}</div>
                    @elseif($order->examType && $order->examType->children->isNotEmpty())
                        <div style="margin-top: 10px;">
                            @foreach($order->examType->children as $child)
                                <div class="exam-item">
                                    <span class="exam-code">[{{ $child->code_fonasa ?? 'S/C' }}]</span>
                                    <span style="font-size: 11px; font-weight: bold;">{{ $child->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    @elseif($order->examType)
                        <div class="exam-item">
                            <span class="exam-code">[{{ $order->examType->code_fonasa ?? 'S/C' }}]</span>
                            <span style="font-size: 11px; font-weight: bold;">{{ $order->examType->name }}</span>
                        </div>
                    @endif

                    <div style="color: #636e72; font-style: italic; font-size: 9px; margin-top: 15px; border-top: 1px solid #edf2f7; padding-top: 8px;">
                        <strong>Nota Importante:</strong> El paciente debe consultar directamente con el laboratorio sobre los requisitos de preparación técnica necesarios.
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

@if($order->type !== 'custom' && $prescription->clinical_context)
<div class="section">
    <table width="100%">
        <tr>
            <td class="section-title">Observaciones Clínicas</td>
            <td style="font-size: 11px; color: #2d3436; white-space: pre-wrap;">{{ $prescription->clinical_context }}</td>
        </tr>
    </table>
</div>
@endif

<div class="footer-fixed">
    <div class="qr-container">
        <table width="100%">
            <tr>
                <td width="70">
                    <img src="data:image/svg+xml;base64,{{ $qrCode }}" style="width: 70px; height: 70px;">
                </td>
                <td style="padding-left: 15px; vertical-align: middle;">
                    <div style="color: #0d6efd; font-weight: bold; font-size: 10px; margin-bottom: 3px;">VERIFICACIÓN DIGITAL</div>
                    <div style="color: #636e72; font-size: 9px; line-height: 1.2;">
                        Escanee el código para confirmar la autenticidad en nuestra plataforma oficial o ingrese el código
                        <strong>{{ $prescription->verification_code }}</strong> en pidetuexamen.cl/v/{{ $prescription->verification_code }}.
                        <br>ID Transacción: {{ strtoupper(substr($order->id, 0, 8)) }}
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div class="legal-footer">
        CODE TECH DIGITAL SPA • RUT 77.736.856-7 • SANTIAGO, CHILE<br>
        Documento Electrónico generado por PideTuExamen.cl el {{ now()->format('d/m/Y H:i') }}
    </div>
</div>

</body>
</html>
