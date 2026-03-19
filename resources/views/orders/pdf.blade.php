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
            background-color: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 18px;
        }

        .document-title {
            padding: 0 50px;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 25px;
            color: #2d3436;
            text-transform: uppercase;
            letter-spacing: 1px;
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

        /* CAMBIO SOLICITADO: Bordes con colores, sin relleno */
        .prestacion-card {
            background-color: transparent;
            border: 1px solid #d1e1f5;
            border-left: 5px solid #0d6efd;
            padding: 18px 22px;
            border-radius: 8px;
        }

        .exam-item {
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #f8f9fa;
            display: block;
        }

        .exam-code {
            color: #0d6efd;
            font-weight: bold;
            font-size: 9px;
            margin-right: 8px;
        }

        .footer-fixed {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 160px;
        }

        .qr-container {
            margin: 0 50px 20px 50px;
            background-color: #ffffff;
            border-radius: 10px;
            padding: 15px;
            border: 1px solid #e9ecef;
        }

        .legal-footer {
            border-top: 1px solid #edf2f7;
            padding: 15px 50px;
            text-align: center;
            font-size: 8.5px;
            color: #b2bec3;
            line-height: 1.5;
        }

        .signature-container { margin-top: 0; text-align: right; }
        .signature-img { max-height: 70px; max-width: 160px; display: inline-block; }
    </style>
</head>
<body>

<div class="top-bar"></div>

<div class="header">
    <table width="100%">
        <tr>
            <td width="60%">
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

<div class="section">
    <table width="100%">
        <tr>
            <td class="section-title">Médico Emisor</td>
            <td>
                <table width="100%">
                    <tr>
                        <td width="65%" style="vertical-align: top;">
                            <div class="value" style="font-size: 15px; margin-bottom: 4px;">
                                Dr(a). {{ $prescription->doctor->user->name }}
                            </div>
                            <div style="color: #636e72; font-size: 10px;">
                                RUT: {{ $prescription->doctor->rut }} | Registro SIS: {{ $prescription->doctor->rnpi_number }}<br>
                                <span style="color: #0d6efd; font-weight: bold; letter-spacing: 0.3px;">
                                    {{ strtoupper($prescription->doctor->specialties->pluck('name')->join(' / ')) }}
                                </span>
                            </div>
                        </td>
                        <td width="35%" align="right" style="vertical-align: middle;">
                            @if(isset($signatureBase64) && $signatureBase64)
                                <div class="signature-container">
                                    <img src="{{ $signatureBase64 }}" class="signature-img">
                                </div>
                            @else
                                <div style="color: #b2bec3; font-style: italic; font-size: 8px; text-align: right;">
                                    Documento firmado electrónicamente<br>bajo Ley N° 19.799
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

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

<div class="document-title" style="margin-top: 10px;">Orden Médica</div>


<div class="section">
    <table width="100%">
        <tr>
            <td class="section-title">Análisis Solicitados</td>
            <td>
                <div class="prestacion-card">
                    <div class="value" style="font-size: 14px; color: #0d6efd; margin-bottom: 12px; border-bottom: 2px solid #f8f9fa; padding-bottom: 8px; letter-spacing: 0.5px;">
                        @if($order->type === 'multiple')
                            PACK DE EXÁMENES SELECCIONADOS
                        @elseif($order->type === 'custom')
                            ORDEN MÉDICA PERSONALIZADA
                        @else
                            {{ strtoupper($order->examType->name ?? 'EXAMEN ESTÁNDAR') }}
                        @endif
                    </div>

                    {{-- Contenido para flujos de texto libre (Multiple o Custom) --}}
                    @if($order->type === 'multiple' || $order->type === 'custom')
                        <div style="font-size: 12px; font-weight: bold; color: #2d3436; line-height: 1.8; padding: 5px 0;">
                            @php
                                $rawText = $prescription->clinical_context ?? $order->custom_description;
                                // Limpiamos el prefijo para que no se repita en cada punto
                                $cleanText = str_replace('Solicitud de exámenes: ', '', $rawText);
                                // Separamos por comas
                                $items = explode(',', $cleanText);
                            @endphp

                            <ul style="list-style-type: none; margin: 0; padding: 0;">
                                @foreach($items as $item)
                                    @if(trim($item) !== '')
                                        <li style="margin-bottom: 6px; display: block;">
                                            <span style="color: #0d6efd; margin-right: 10px;">•</span>
                                            {{ trim($item) }}
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>

                    {{-- Contenido para flujos estándar (Perfiles con hijos) --}}
                    @elseif($order->examType && $order->examType->children->isNotEmpty())
                        <div style="margin-top: 5px;">
                            @foreach($order->examType->children as $child)
                                <div class="exam-item">
                                    <span class="exam-code">[{{ $child->code_fonasa ?? 'S/C' }}]</span>
                                    <span style="font-size: 11px; font-weight: bold;">{{ $child->name }}</span>
                                </div>
                            @endforeach
                        </div>

                    {{-- Contenido para examen único --}}
                    @elseif($order->examType)
                        <div class="exam-item" style="border-bottom: none;">
                            <span class="exam-code">[{{ $order->examType->code_fonasa ?? 'S/C' }}]</span>
                            <span style="font-size: 11px; font-weight: bold;">{{ $order->examType->name }}</span>
                        </div>
                    @endif

                    <div style="color: #636e72; font-style: italic; font-size: 8.5px; margin-top: 15px; border-top: 1px solid #edf2f7; padding-top: 10px;">
                        <strong>Nota Importante:</strong> El paciente debe consultar directamente con el laboratorio sobre los requisitos de preparación técnica necesarios para la toma de muestras.
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>






{{-- Solo mostrar sección de Observaciones si NO es multiple/custom y hay texto --}}
@if(!in_array($order->type, ['multiple', 'custom']) && $prescription->clinical_context)
<div class="section">
    <table width="100%">
        <tr>
            <td class="section-title">Observaciones</td>
            <td style="font-size: 11px; color: #2d3436; white-space: pre-wrap; line-height: 1.5;">
                {{ $prescription->clinical_context }}
            </td>
        </tr>
    </table>
</div>
@endif


{{-- Ocultar sección de observaciones si es multiple, ya que el contenido va arriba --}}
@if($order->type !== 'multiple' && $order->type !== 'custom' && $prescription->clinical_context)
<div class="section">
    <table width="100%">
        <tr>
            <td class="section-title">Observaciones</td>
            <td style="font-size: 11px; color: #2d3436; white-space: pre-wrap; line-height: 1.5;">{{ $prescription->clinical_context }}</td>
        </tr>
    </table>
</div>
@endif

@if($order->type !== 'custom' && $prescription->clinical_context)
<div class="section">
    <table width="100%">
        <tr>
            <td class="section-title">Observaciones</td>
            <td style="font-size: 11px; color: #2d3436; white-space: pre-wrap; line-height: 1.5;">{{ $prescription->clinical_context }}</td>
        </tr>
    </table>
</div>
@endif

<div class="footer-fixed">
    <div class="qr-container">
        <table width="100%">
            <tr>
                <td width="75">
                    <img src="data:image/svg+xml;base64,{{ $qrCode }}" style="width: 70px; height: 70px;">
                </td>
                <td style="padding-left: 15px; vertical-align: middle;">
                    <div style="color: #0d6efd; font-weight: bold; font-size: 10px; margin-bottom: 4px; letter-spacing: 0.5px;">VERIFICACIÓN DIGITAL</div>
                    <div style="color: #636e72; font-size: 9px; line-height: 1.3;">
                        Escanee el código QR para confirmar la autenticidad en nuestra plataforma o ingrese el código
                        <strong style="color: #2d3436;">{{ $prescription->verification_code }}</strong> en pidetuexamen.cl/verificar.
                        <br>ID Transacción: <span style="font-family: monospace;">{{ strtoupper(substr($order->id, 0, 8)) }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div class="legal-footer">
        CODE TECH DIGITAL SPA • RUT 77.736.856-7 • SANTIAGO, CHILE<br>
        Documento Electrónico generado por PideTuExamen.cl el {{ now()->format('d/m/Y H:i') }} hrs.
    </div>
</div>

</body>
</html>
