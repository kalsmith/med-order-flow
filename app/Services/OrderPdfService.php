<?php

namespace App\Services;

use App\Models\MedicalOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd; // O SvgImageBackEnd si no tienes Imagick
use BaconQrCode\Writer;

class OrderPdfService
{
    public function generate(MedicalOrder $order)
    {
        $order->loadMissing(['patient.user', 'doctor.user', 'examType']);

        // 1. Configuramos el renderizado usando Imagick (estándar en la mayoría de servidores)
        $renderer = new ImageRenderer(
            new RendererStyle(120),
            new ImagickImageBackEnd()
        );

        $writer = new Writer($renderer);

        // 2. Generamos el QR con la URL de validación.
        // Según los ejemplos reales, el QR es clave para la integridad[cite: 16, 23].
        $qrRaw = $writer->writeString(route('validate.order', $order->id));
        $qrCode = base64_encode($qrRaw);

        // 3. Retornar la vista con los datos del paciente y médico [cite: 4, 12, 21, 27]
        return Pdf::loadView('orders.pdf', [
            'order' => $order,
            'qrCode' => $qrCode
        ]);
    }
}
