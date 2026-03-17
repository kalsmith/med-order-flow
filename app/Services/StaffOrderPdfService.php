<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;

class StaffOrderPdfService
{
    public function generate(Order $order)
    {
        // 1. Cargar relaciones necesarias para Staff
        $order->loadMissing([
            'patient',
            'activePrescription.doctor.user',
            'activePrescription.doctor.specialties',
            'examType.children'
        ]);

        $prescription = $order->activePrescription;

        if (!$prescription) {
            throw new \Exception("No hay una prescripción válida para generar el PDF.");
        }

        // 2. Procesar Firma del Médico (Base64 para DomPDF)
        $signatureBase64 = null;
        $doctor = $prescription->doctor;
        if ($doctor && $doctor->signature_path && Storage::disk('public')->exists($doctor->signature_path)) {
            $fileContent = Storage::disk('public')->get($doctor->signature_path);
            $mimeType = Storage::disk('public')->mimeType($doctor->signature_path);
            $signatureBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($fileContent);
        }

        // 3. Generar Código QR de Verificación
        $renderer = new ImageRenderer(new RendererStyle(120, 0), new SvgImageBackEnd());
        $writer = new Writer($renderer);
        $url = route('validate.order', ['id' => $prescription->verification_code]);
        $qrCode = base64_encode($writer->writeString($url));

        // 4. Retornar el PDF usando la nueva vista exclusiva de Staff
        return Pdf::loadView('orders.pdf-staff', [
            'order' => $order,
            'prescription' => $prescription,
            'qrCode' => $qrCode,
            'signatureBase64' => $signatureBase64,
            'isAudit' => true // Variable para mostrar marcas de agua si lo deseas
        ])->setPaper('a4')
          ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif'
          ]);
    }
}
