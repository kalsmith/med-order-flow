<?php

namespace App\Services;

use App\Models\MedicalOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Writer;

class OrderPdfService
{
    public function generate(MedicalOrder $order)
    {
        $order->loadMissing(['patient.user', 'doctor.user', 'doctor.specialties', 'examType']);

        $renderer = new ImageRenderer(
            new RendererStyle(120),
            new ImagickImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrRaw = $writer->writeString(route('validate.order', $order->id));
        $qrCode = base64_encode($qrRaw);

        return Pdf::loadView('orders.pdf', [
            'order' => $order,
            'qrCode' => $qrCode
        ])->setOptions([
            'isHtml5ParserEnabled' => true,      // Permite procesar el CSS más moderno
            'isRemoteEnabled' => true,           // Crucial para cargar archivos locales vía rutas absolutas
            'isFontSubsettingEnabled' => true,   // Solo incluye los caracteres usados (reduce peso del PDF)
            'fontCache' => storage_path('fonts') // Asegura que dompdf tenga un lugar donde escribir el caché de Roboto
        ]);
    }
}
