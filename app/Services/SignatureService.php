<?php

namespace App\Services;

use App\Models\MedicalOrder;
use Illuminate\Support\Facades\Log;

class SignatureService
{
    // Cambia esto a true cuando quieras probar el "Ciclo Fallido"
    protected $forceFailure = false;

    public function sign(MedicalOrder $order)
    {
        if ($this->forceFailure) {
            Log::error("SERVICE [SIMULACIÓN]: Error forzado en la firma para orden: " . $order->id);
            // Simulamos que la API de FEA devuelve un error
            return (object) [
                'success' => false,
                'message' => 'Error de conexión con el proveedor de firma'
            ];
        }

        // --- Happy Path ---
        Log::info("SERVICE: Iniciando proceso de firma para orden: " . $order->id);
        Log::info("FIRMADO: La orden {$order->id} ha sido firmada digitalmente.");

        return (object) [
            'success' => true,
            'signature_id' => 'FEA-' . uniqid()
        ];
    }

    public function notify(MedicalOrder $order)
    {
        // Solo enviamos notificaciones si el proceso fue exitoso
        Log::info("MAIL ENVIADO: Notificación enviada al paciente para la orden: " . $order->id);
    }
}
