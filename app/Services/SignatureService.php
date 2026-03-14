<?php

namespace App\Services;

use App\Models\Prescription; // Cambiado de MedicalOrder
use Illuminate\Support\Facades\Log;

class SignatureService
{
    protected $forceFailure = true;

    /**
     * Firma la prescripción médica (antes vinculada a MedicalOrder)
     */
    public function sign(Prescription $prescription)
    {
        // 1. Cargamos el doctor y su relación de usuario para el nombre
        $prescription->load(['doctor.user', 'examType']);

        // 2. Validación de seguridad: ¿Tiene un doctor asignado?
        if (!$prescription->doctor_id || !$prescription->doctor) {
            Log::error("SERVICE ERROR: La prescripción {$prescription->id} no tiene un médico asignado para firmar.");
            return (object) [
                'success' => false,
                'message' => 'No hay un profesional responsable asignado a esta prescripción.'
            ];
        }

        $doctor = $prescription->doctor;

        // 3. Simulación de fallo forzado (para pruebas de robustez)
        if ($this->forceFailure) {
            Log::error("SERVICE [SIMULACIÓN]: Error forzado en la firma para prescripción: " . $prescription->id);
            return (object) [
                'success' => false,
                'message' => 'Error de conexión con el proveedor de firma'
            ];
        }

        // --- Happy Path (Simulación de Firma Electrónica) ---

        Log::info("SERVICE: Iniciando proceso de firma para la prescripción: " . $prescription->id);
        Log::info("MÉDICO FIRMANTE: " . ($doctor->user->name ?? 'N/A') . " (RUT: " . $doctor->rut . ")");

        // Actualizamos el estado de la prescripción a 'signed' (si usas ese estado)
        // o guardamos los datos de la firma en metadata.
        $prescription->update([
            'status' => 'signed',
            // 'signed_at' => now(), // Si tienes este campo
        ]);

        return (object) [
            'success' => true,
            'signature_id' => 'FEA-' . strtoupper(uniqid()),
            'doctor_name'  => $doctor->user->name ?? 'Médico Asignado'
        ];
    }

    /**
     * Notifica al paciente (ahora a través de la relación con Order)
     */
    public function notify(Prescription $prescription)
    {
        $prescription->load('order.patient.user');
        $email = $prescription->order->patient->user->email;

        Log::info("MAIL ENVIADO: Notificación enviada a {$email} para la prescripción: " . $prescription->id);
    }
}
