<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>

@page { margin:0; }

body{
    font-family: Helvetica, Arial, sans-serif;
    margin:0;
    font-size:11px;
    color:#1f2937;
}

/* BARRA SUPERIOR */

.top-bar{
    height:6px;
    background:#6366f1;
}

/* HEADER */

.header{
    padding:25px 40px 15px 40px;
}

.header-table{
    width:100%;
}

.logo{
    font-size:24px;
    font-weight:800;
    color:#1e293b;
}

.subtitle{
    font-size:9px;
    color:#64748b;
}

.header-right{
    text-align:right;
    font-size:10px;
}

.meta-label{
    color:#6b7280;
}

/* TITLE */

.title{
    margin:10px 40px 15px 40px;
    font-size:18px;
    font-weight:700;
}

/* LINE */

.line{
    border-top:1px solid #e5e7eb;
    margin:0 40px 10px 40px;
}

/* INFO */

.info-table{
    width:100%;
    margin:0 40px;
}

.info-label{
    font-size:9px;
    color:#6b7280;
    text-transform:uppercase;
    font-weight:700;
}

.info-value{
    font-weight:600;
}

/* SECTIONS */

.section{
    margin:20px 40px;
}

.section-title{
    font-size:10px;
    color:#6b7280;
    text-transform:uppercase;
    font-weight:700;
    margin-bottom:8px;
}

/* PRESTACIONES */

.exam{
    padding-left:10px;
}

/* PROFESIONAL */

.prof-table{
    width:100%;
}

.prof-name{
    font-weight:700;
}

.prof-meta{
    font-size:10px;
    color:#4b5563;
}

/* FIRMA */

.signature{
    max-height:60px;
}

.signature-line{
    border-top:1px solid #d1d5db;
    width:160px;
    margin-top:5px;
}

/* QR */

.qr-section{
    margin:25px 40px;
}

.qr-table{
    width:100%;
}

.qr-text{
    font-size:10px;
    color:#4b5563;
}

/* FOOTER */

.footer{
    border-top:1px solid #e5e7eb;
    padding:10px 40px;
    font-size:9px;
    color:#6b7280;
}

</style>
</head>

<body>

<div class="top-bar"></div>

<!-- HEADER -->

<div class="header">

<table class="header-table">

<tr>

<td>
<div class="logo">Doctor911</div>
<div class="subtitle">
Los Leones 787, Providencia Santiago · +56999999999
</div>
</td>

<td class="header-right">

<span class="meta-label">Fecha Emisión</span><br>
{{ $order->created_at->format('d-m-Y') }}

<br><br>

<span class="meta-label">ID</span><br>
{{ strtoupper(substr($order->id,0,12)) }}

</td>

</tr>

</table>

</div>

<div class="title">Orden médica</div>

<div class="line"></div>

<!-- PACIENTE -->

<table class="info-table">

<tr>
<td width="40%">
<div class="info-label">Paciente</div>
<div class="info-value">{{ $order->patient->full_name }}</div>
</td>

<td width="20%">
<div class="info-label">Edad</div>
<div class="info-value">{{ $order->patient->age }}</div>
</td>
</tr>

<tr><td colspan="2" height="12"></td></tr>

<tr>
<td>
<div class="info-label">RUT</div>
<div class="info-value">{{ $order->patient->rut }}</div>
</td>
</tr>

<tr><td colspan="2" height="12"></td></tr>

<tr>
<td colspan="2">
<div class="info-label">Dirección</div>
<div class="info-value">{{ $order->patient->address }}</div>
</td>
</tr>

</table>

<!-- PRESTACIONES -->

<div class="section">

<div class="section-title">Prestaciones</div>

<div class="exam">
<strong>{{ $order->clinical_context }}</strong>

<br><br>

Prestación autorizada para ser realizada en cualquier centro de salud en convenio.
</div>

</div>

<div class="line"></div>

<!-- NOTAS -->

<div class="section">

<div class="section-title">Notas</div>

Sin notas

</div>

<div class="line"></div>

<!-- PROFESIONAL -->

<div class="section">

<table class="prof-table">

<tr>

<td>

<div class="section-title">Profesional</div>

<div class="prof-name">
Dr. {{ $order->doctor->user->name }}
</div>

<div class="prof-meta">

RUT {{ $order->doctor->rut }}<br>
Reg. SIS {{ $order->doctor->rnpi_number }}<br>

@foreach($order->doctor->specialties as $specialty)
{{ $specialty->name }}{{ !$loop->last ? ' / ' : '' }}
@endforeach

</div>

</td>

<td align="right">

<div class="section-title">Firma</div>

@if($order->doctor->signature_path)

<img
src="{{ public_path('storage/' . $order->doctor->signature_path) }}"
class="signature">

@endif

<div class="signature-line"></div>

</td>

</tr>

</table>

</div>

<!-- QR -->

<div class="qr-section">

<table class="qr-table">

<tr>

<td width="80">

<img src="data:image/png;base64,{{ $qrCode }}" width="70">

</td>

<td class="qr-text">

Para validar la veracidad de este documento escanee el código QR o ingrese el ID del documento en el portal Doctor911.

</td>

</tr>

</table>

</div>

<!-- FOOTER -->

<div class="footer">

CODE TECH DIGITAL SPA · Doctor911 · RUT 77.736.856-7

</div>

</body>
</html>
