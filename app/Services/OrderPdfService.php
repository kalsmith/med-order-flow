<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd; // Cambiamos a SVG
use BaconQrCode\Writer;

class OrderPdfService
{
    /**
     * Genera el PDF basado en la orden y su receta firmada.
     */
    public function generate(Order $order)
    {
        // 1. CARGA DE RELACIONES
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

        // 2. GENERACIÓN DE QR EN FORMATO SVG
        // El formato SVG es más compatible y no depende de extensiones de imagen en el servidor
        $renderer = new ImageRenderer(
            new RendererStyle(150, 0),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        // Generamos la URL de validación
        $url = route('validate.order', ['id' => $prescription->verification_code]);

        // Escribimos el QR y lo pasamos a Base64 para que DomPDF lo renderice sin problemas
        $qrRaw = $writer->writeString($url);
        $qrCode = base64_encode($qrRaw);

        // 3. CONFIGURACIÓN DOMPDF
        return Pdf::loadView('orders.pdf', [
            'order' => $order,
            'prescription' => $prescription,
            'qrCode' => $qrCode
        ])->setPaper('a4')
          ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);
    }
}
