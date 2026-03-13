<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">

<style>

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

@font-face {
    font-family: 'Roboto';
    src: url("{{ public_path('fonts/Roboto-Black.ttf') }}") format('truetype');
    font-weight: 900;
}

@page {
    margin:0px;
}

body{
    font-family:'Roboto', Helvetica, sans-serif;
    margin:0;
    font-size:11px;
    color:#2d3436;
    line-height:1.4;
    background:#ffffff;
}

/* WATERMARK */

.watermark{
    position:absolute;
    top:45%;
    left:50%;
    transform:translate(-50%,-50%);
    font-size:350px;
    color:#f1f3f5;
    z-index:-1;
    font-weight:900;
}

/* TOP BAR */

.top-bar{
    height:6px;
    background:#0d6efd;
}

/* HEADER */

.header{
    padding:30px 50px 20px 50px;
}

.logo{
    font-size:28px;
    font-weight:900;
    color:#0d6efd;
}

.text-muted{
    color:#6c757d;
}

.id-badge{
    background:#f8f9fa;
    border:1px solid #e9ecef;
    border-radius:6px;
    padding:12px 18px;
}

.label{
    font-size:9px;
    color:#6c757d;
    text-transform:uppercase;
    font-weight:bold;
}

.value{
    font-size:13px;
    font-weight:bold;
    color:#2d3436;
}

/* TITLE */

.document-title{
    padding:0 50px;
    font-size:24px;
    font-weight:900;
    margin-bottom:35px;
}

/* SECTIONS */

.section{
    margin:0 50px 28px 50px;
    border-top:1px solid #edf2f7;
    padding-top:18px;
}

.section-title{
    width:130px;
    font-size:10px;
    font-weight:900;
    color:#0d6efd;
    text-transform:uppercase;
    letter-spacing:0.8px;
    vertical-align:top;
}

/* DOCTOR CARD */

.doctor-card{
    background:#f8f9fa;
    border-radius:8px;
    padding:15px 18px;
    border:1px solid #e9ecef;
}

/* PRESTACION */

.prestacion-card{
    background:#f8fbff;
    border-left:5px solid #0d6efd;
    padding:18px;
    border-radius:6px;
}

/* SIGNATURE */

.signature{
    max-height:60px;
    margin-top:6px;
}

.signature-line{
    border-top:1px solid #ced4da;
    width:180px;
    margin-top:6px;
}

/* QR */

.qr-container{
    margin:35px 50px 25px 50px;
    background:#f8f9fa;
    border-radius:10px;
    padding:20px;
    border:1px solid #e9ecef;
}

/* FOOTER */

.legal-footer{
    border-top:1px solid #edf2f7;
    padding:15px 50px;
    text-align:center;
    font-size:9px;
    color:#95a5a6;
    font-weight:bold;
}

</style>

</head>

<body>

<div class="watermark">Rp</div>

<div class="top-bar"></div>

<!-- HEADER -->

<div class="header">

<table width="100%">

<tr>

<td width="60%">

<div class="logo">Doctor911</div>

<div class="text-muted" style="font-size:10px;margin-top:5px;">
doctor911.cl • contacto@doctor911.cl<br>
+56 9 1234 5678
</div>

</td>

<td width="40%">

<div class="id-badge">

<table width="100%">

<tr>

<td>

<div class="label">Emisión</div>
<div class="value">{{ $order->created_at->format('d/m/Y') }}</div>

</td>

<td align="right">

<div class="label">ID Orden</div>
<div class="value" style="color:#0d6efd;font-family:monospace;">
#{{ strtoupper(substr($order->id,0,8)) }}
</div>

</td>

</tr>

</table>

</div>

</td>

</tr>

</table>

</div>

<div class="document-title">Orden médica</div>

<!-- DOCTOR -->

<div class="section">

<table width="100%">

<tr>

<td class="section-title">
Médico Emisor
</td>

<td>

<div class="doctor-card">

<div class="value" style="font-size:16px;margin-bottom:6px;">
Dr. {{ $order->doctor->user->name }}
</div>

<div class="text-muted">

RUT: {{ $order->doctor->rut }} |
Registro SIS: {{ $order->doctor->rnpi_number }}

<br>

<strong style="color:#0d6efd;">
@foreach($order->doctor->specialties as $specialty)
{{ strtoupper($specialty->name) }}{{ !$loop->last ? ' / ' : '' }}
@endforeach
</strong>

</div>

@if($order->doctor->signature_path)

<img
src="{{ public_path('storage/' . $order->doctor->signature_path) }}"
class="signature">

@endif

<div class="signature-line"></div>

</div>

</td>

</tr>

</table>

</div>

<!-- PACIENTE -->

<div class="section">

<table width="100%">

<tr>

<td class="section-title">
Paciente
</td>

<td>

<table width="100%">

<tr>

<td width="45%">

<div class="label">Nombre</div>
<div class="value">{{ strtoupper($order->patient->full_name) }}</div>

</td>

<td width="30%">

<div class="label">RUT</div>
<div class="value">{{ $order->patient->rut }}</div>

</td>

<td>

<div class="label">Edad</div>
<div class="value">{{ $order->patient->age }} años</div>

</td>

</tr>

</table>

</td>

</tr>

</table>

</div>

<!-- PRESTACIONES -->

<div class="section">

<table width="100%">

<tr>

<td class="section-title">
Prestaciones
</td>

<td>

<div class="prestacion-card">

<div class="value" style="font-size:16px;color:#0d6efd;margin-bottom:6px;">
{{ $order->clinical_context }}
</div>

<div class="text-muted" style="font-style:italic;font-size:10px;">
Esta orden es válida para su uso en centros de salud, laboratorios y toma de muestras a nivel nacional.
</div>

</div>

</td>

</tr>

</table>

</div>

<!-- QR -->

<div class="qr-container">

<table width="100%">

<tr>

<td width="90">

<img
src="data:image/png;base64,{{ $qrCode }}"
style="width:80px;height:80px;">

</td>

<td style="padding-left:20px;vertical-align:middle;">

<div style="color:#0d6efd;font-weight:900;font-size:11px;margin-bottom:4px;">
DOCUMENTO VERIFICADO DIGITALMENTE
</div>

<div class="text-muted" style="font-size:10px;line-height:1.3;">

Para confirmar la validez de esta orden médica escanee el código QR
o visite <strong>doctor911.cl/validar</strong> e ingrese el ID de seguimiento.

</div>

</td>

</tr>

</table>

</div>

<div class="legal-footer">

CODE TECH DIGITAL SPA • RUT 77.736.856-7 • SANTIAGO, CHILE

</div>

</body>
</html>
