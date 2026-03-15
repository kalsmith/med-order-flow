<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Writer;

class OrderPdfService
{
    /**
     * Genera el PDF basado en la orden y su receta firmada.
     */
    public function generate(Order $order)
    {
        // 1. CARGA DE RELACIONES: Usamos la relación activePrescription definida en tu modelo Order
        $order->loadMissing([
            'patient',
            'activePrescription.doctor.user',
            'activePrescription.doctor.specialties',
            'activePrescription.examType.children',
        ]);

        $prescription = $order->activePrescription;

        if (!$prescription) {
            throw new \Exception("No existe una receta firmada para esta orden.");
        }

        // 2. GENERACIÓN DE QR: Apuntando a la validación pública
        // Cambiamos el render a Imagick o Svg según soporte, PNG es lo más seguro.
        $renderer = new ImageRenderer(
            new RendererStyle(150, 0),
            new ImagickImageBackEnd()
        );

        $writer = new Writer($renderer);

        // URL de validación usando el código de verificación único de la receta
        $url = route('validate.order', ['code' => $prescription->verification_code]);

        $qrRaw = $writer->writeString($url);
        $qrCode = base64_encode($qrRaw);

        // 3. CONFIGURACIÓN DOMPDF
        return Pdf::loadView('orders.pdf', [
            'order' => $order,
            'prescription' => $prescription, // Pasamos la receta explícitamente a la vista
            'qrCode' => $qrCode
        ])->setPaper('a4')
          ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);
    }
}
