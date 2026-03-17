<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage; // Añadimos esto

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

        // --- LÓGICA PARA LA FIRMA EN BASE64 ---
        $signatureBase64 = null;
        $doctor = $prescription->doctor;

        if ($doctor && $doctor->signature_path) {
            // Buscamos el archivo en el disco public
            if (Storage::disk('public')->exists($doctor->signature_path)) {
                $fileContent = Storage::disk('public')->get($doctor->signature_path);
                $mimeType = Storage::disk('public')->mimeType($doctor->signature_path);
                $signatureBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($fileContent);
            }
        }
        // --------------------------------------

        // 2. GENERACIÓN DE QR EN FORMATO SVG
        $renderer = new ImageRenderer(
            new RendererStyle(150, 0),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $url = route('validate.order', ['id' => $prescription->verification_code]);
        $qrRaw = $writer->writeString($url);
        $qrCode = base64_encode($qrRaw);

        // 3. CONFIGURACIÓN DOMPDF
        return Pdf::loadView('orders.pdf', [
            'order' => $order,
            'prescription' => $prescription,
            'qrCode' => $qrCode,
            'signatureBase64' => $signatureBase64 // Pasamos la nueva variable
        ])->setPaper('a4')
          ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);
    }
}
