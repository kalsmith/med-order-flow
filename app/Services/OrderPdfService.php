<?php

namespace App\Services;

use App\Models\MedicalOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode; // Sugerencia de cambio más abajo
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Writer;

class OrderPdfService
{
    public function generate(MedicalOrder $order)
    {
        // 1. CARGA DE RELACIONES: Agregamos 'patient.user' por si usas el nombre real del usuario
        // y verificamos que 'examType' esté presente para evitar el error de "propiedad en null"
        $order->loadMissing([
            'patient',
            'doctor.user',
            'doctor.specialties',
            'examType'
        ]);

        // 2. GENERACIÓN DE QR:
        // Tip: Si usas ImagickImageBackEnd, asegúrate de que la extensión php-imagick esté instalada en el server.
        // Si tienes problemas en el servidor (como Docker o VPS básicos), PNG suele ser más compatible.
        $renderer = new ImageRenderer(
            new RendererStyle(150, 0), // Aumentamos un poco el tamaño y quitamos margen (quiet zone)
            new ImagickImageBackEnd()
        );

        $writer = new Writer($renderer);

        // Usamos el verification_code en la URL si lo prefieres, es más corto que el UUID
        $url = route('validate.order', ['id' => $order->id]);

        $qrRaw = $writer->writeString($url);
        $qrCode = base64_encode($qrRaw);

        // 3. CONFIGURACIÓN DOMPDF
        return Pdf::loadView('orders.pdf', [
            'order' => $order,
            'qrCode' => $qrCode
        ])->setPaper('a4') // IMPORTANTE: Define el tamaño del papel explícitamente
          ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif', // Font de respaldo si falla Roboto
            'isFontSubsettingEnabled' => true,
            'fontCache' => storage_path('fonts')
        ]);
    }
}
