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
        // Cargamos especialidades del doctor para mostrarlas en el pie de firma
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
        ]);
    }
}
