<?php

namespace App\Http\Controllers;

use App\Models\GatewayTransaction;
use App\Models\MedicalOrder;
use App\Services\FlowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // <--- Importante: Para poder escribir en los logs

class CheckoutController extends Controller
{
    protected $flow;

    public function __construct(FlowService $flow)
    {
        $this->flow = $flow;
    }

    public function processMedicalOrderPayment(MedicalOrder $order)
    {
        Log::info('Iniciando proceso de pago para orden: ' . $order->id);

        $response = $this->flow->createPayment($order);

        if ($response && isset($response->token)) {
            Log::info('Redirigiendo a Flow para la orden: ' . $order->id . ' con token: ' . $response->token);
            return redirect()->away($response->url . "?token=" . $response->token);
        }

        Log::error('Fallo al crear pago en Flow para la orden: ' . $order->id);
        return back()->with('error', 'Error al procesar el pago con Flow.');
    }

    public function handleWebhook(Request $request)
    {
        // Log básico para verificar si llega la petición de Flow
        Log::info('Webhook recibido de Flow. Token recibido: ' . ($request->token ?? 'NULO'));

        try {
            $processed = $this->flow->handleWebhook($request->token);

            if ($processed) {
                Log::info('Webhook procesado exitosamente para token: ' . $request->token);
                return response('OK', 200);
            } else {
                Log::warning('Webhook procesado pero retornó FALSE para token: ' . $request->token);
                return response('Error', 400);
            }
        } catch (\Exception $e) {
            Log::error('ERROR CRÍTICO en Webhook: ' . $e->getMessage());
            return response('Internal Server Error', 500);
        }
    }

    public function flowReturn(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->route('home')->with('error', 'Token no recibido.');
        }

        // BUSCAMOS EN LA TABLA DE TRANSACCIONES, NO EN LA ORDEN
        $gatewayTrx = GatewayTransaction::where('token', $token)->first();

        if (!$gatewayTrx) {
            Log::error("No se encontró transacción para el token: $token");
            return redirect()->route('home')->with('error', 'Transacción no encontrada.');
        }

        // OBTENEMOS LA ORDEN A TRAVÉS DE LA TRANSACCIÓN
        // Asumiendo que tu transacción tiene un 'payable' o un 'medical_order_id'
        $order = $gatewayTrx->payable; // o $gatewayTrx->medicalOrder

        if (!$order) {
            return redirect()->route('home')->with('error', 'Orden no encontrada.');
        }

        return redirect()->route('payment.success', ['order' => $order->id]);
    }




}
