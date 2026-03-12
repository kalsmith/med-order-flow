<?php

namespace App\Services;

use App\Models\MedicalOrder;
use Illuminate\Support\Facades\Log;

class SignatureService
{
    protected $forceFailure = true;

    public function sign(MedicalOrder $order)
    {
        // 1. Cargamos el doctor asignado a la orden
        $order->load('doctor');

        // 2. Validación de seguridad: ¿Tiene un doctor asignado?
        if (!$order->doctor_id || !$order->doctor) {
            Log::error("SERVICE ERROR: La orden {$order->id} no tiene un médico asignado para firmar.");
            return (object) [
                'success' => false,
                'message' => 'No hay un profesional responsable asignado a esta orden.'
            ];
        }

        $doctor = $order->doctor;

        // 3. Simulación de fallo forzado
        if ($this->forceFailure) {
            Log::error("SERVICE [SIMULACIÓN]: Error forzado en la firma para orden: " . $order->id);
            return (object) [
                'success' => false,
                'message' => 'Error de conexión con el proveedor de firma'
            ];
        }

        // --- Happy Path ---
        // Aquí es donde en el futuro llamarías a la API de Firma Electrónica Avanzada (FEA)
        // enviando el RUT del doctor ($doctor->rut) y el documento.

        Log::info("SERVICE: Iniciando proceso de firma para la orden: " . $order->id);
        Log::info("MÉDICO FIRMANTE: " . $doctor->user->name . " (RUT: " . $doctor->rut . ")");
        Log::info("FIRMADO: La orden {$order->id} ha sido firmada con el archivo: " . ($doctor->signature_path ?? 'Sin imagen de firma'));

        return (object) [
            'success' => true,
            'signature_id' => 'FEA-' . strtoupper(uniqid()),
            'doctor_name'  => $doctor->user->name // Dato útil para el PDF
        ];
    }

    public function notify(MedicalOrder $order)
    {
        Log::info("MAIL ENVIADO: Notificación enviada al paciente para la orden: " . $order->id);
    }
}
