<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\PngImageBackEnd; // Cambiado por compatibilidad
use BaconQrCode\Writer;

class OrderPdfService
{
    /**
     * Genera el PDF basado en la orden y su receta firmada.
     */
    public function generate(Order $order)
    {
        // 1. CARGA DE RELACIONES: Aseguramos que todo esté disponible para la vista
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

        // 2. GENERACIÓN DE QR
        // Usamos PngImageBackEnd para asegurar que DomPDF lo procese correctamente
        $renderer = new ImageRenderer(
            new RendererStyle(150, 0),
            new PngImageBackEnd()
        );

        $writer = new Writer($renderer);

        /**
         * SOLUCIÓN AL ERROR:
         * Tu ruta en web.php dice: [URI: v/{id}]
         * Por lo tanto, el parámetro DEBE llamarse 'id'.
         * Pasamos el verification_code como el ID que espera la URL de validación.
         */
        $url = route('validate.order', ['id' => $prescription->verification_code]);

        $qrRaw = $writer->writeString($url);
        $qrCode = base64_encode($qrRaw);

        // 3. CONFIGURACIÓN DOMPDF
        // Asegúrate de que la vista en 'resources/views/orders/pdf.blade.php'
        // coincida con el código HTML que actualizamos anteriormente.
        return Pdf::loadView('orders.pdf', [
            'order' => $order,
            'prescription' => $prescription,
            'qrCode' => $qrCode
        ])->setPaper('a4')
          ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true, // Necesario para cargar el logo desde una URL externa
            'defaultFont' => 'Roboto',
        ]);
    }
}
