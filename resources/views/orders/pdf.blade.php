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

/* HEADER */

.header{
    border-top:6px solid #6366f1;
    padding:25px 40px 10px 40px;
}

.logo{
    font-size:22px;
    font-weight:800;
    color:#1e293b;
}

.subtitle{
    font-size:9px;
    color:#64748b;
}

.header-table{
    width:100%;
}

.header-meta{
    text-align:right;
    font-size:10px;
}

.meta-label{
    color:#6b7280;
}

/* TITLE */

.title{
    margin:10px 40px 20px 40px;
    font-size:18px;
    font-weight:700;
}

/* INFO GRID */

.info-box{
    margin:0 40px;
    border-top:1px solid #e5e7eb;
    border-bottom:1px solid #e5e7eb;
}

.info-row{
    display:flex;
    padding:8px 0;
}

.info-label{
    width:120px;
    font-size:9px;
    text-transform:uppercase;
    color:#6b7280;
    font-weight:700;
}

.info-value{
    flex:1;
    font-weight:600;
}

.info-right{
    width:120px;
}

/* PRESTACIONES */

.section{
    margin:20px 40px;
}

.section-title{
    font-size:10px;
    font-weight:700;
    text-transform:uppercase;
    color:#6b7280;
    margin-bottom:8px;
}

.exam{
    margin-bottom:10px;
}

.exam-name{
    font-weight:700;
    color:#1e40af;
}

.exam-prep{
    font-size:10px;
    color:#4b5563;
}

/* NOTES */

.notes{
    border-top:1px solid #e5e7eb;
    padding-top:8px;
}

/* PROFESSIONAL */

.prof-box{
    margin:25px 40px;
    border-top:1px solid #e5e7eb;
    padding-top:10px;
}

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

.signature{
    max-width:120px;
}

/* QR */

.qr-box{
    margin:20px 40px;
    display:flex;
    align-items:center;
}

.qr-text{
    font-size:10px;
    margin-left:10px;
    color:#4b5563;
}

/* FOOTER */

.footer{
    margin-top:20px;
    border-top:1px solid #e5e7eb;
    padding:10px 40px;
    font-size:9px;
    color:#6b7280;
}

</style>
</head>

<body>

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

<td class="header-meta">
<div><span class="meta-label">Fecha Emisión</span><br>
{{ $order->created_at->format('d-m-Y') }}</div>

<br>

<div><span class="meta-label">ID</span><br>
{{ strtoupper(substr($order->id,0,12)) }}</div>
</td>

</tr>

</table>

</div>

<!-- TITLE -->

<div class="title">Orden médica</div>

<!-- INFO -->

<div class="info-box">

<div class="info-row">
<div class="info-label">Paciente</div>
<div class="info-value">{{ $order->patient->full_name }}</div>

<div class="info-right">
<span class="meta-label">Edad</span><br>
{{ $order->patient->age }}
</div>
</div>

<div class="info-row">
<div class="info-label">RUT</div>
<div class="info-value">{{ $order->patient->rut }}</div>
</div>

<div class="info-row">
<div class="info-label">Dirección</div>
<div class="info-value">{{ $order->patient->address }}</div>
</div>

</div>

<!-- PRESTACIONES -->

<div class="section">

<div class="section-title">Prestaciones</div>

<div class="exam">
<div class="exam-name">
{{ $order->clinical_context }}
</div>

<div class="exam-prep">
Prestación autorizada para ser realizada en cualquier centro de salud en convenio.
</div>
</div>

</div>

<!-- NOTES -->

<div class="section notes">

<div class="section-title">Notas</div>

<div>
Sin notas
</div>

</div>

<!-- PROFESIONAL -->

<div class="prof-box">

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

</td>

</tr>

</table>

</div>

<!-- QR -->

<div class="qr-box">

<img src="data:image/png;base64,{{ $qrCode }}" width="70">

<div class="qr-text">
Para validar la veracidad de este documento escanee el código QR
o ingrese el ID del documento en el portal Doctor911.
</div>

</div>

<!-- FOOTER -->

<div class="footer">

CODE TECH DIGITAL SPA · Doctor911 · RUT 77.736.856-7

</div>

</body>
</html>
