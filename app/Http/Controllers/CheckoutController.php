<?php

namespace App\Http\Controllers;

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
    // 1. Logs para saber qué rayos está llegando realmente (Debug intenso)
    Log::info('Entrando en flowReturn', ['query' => $request->query(), 'all' => $request->all()]);

    // 2. Intentamos obtener el token de varias formas (para ser más flexibles)
    $token = $request->query('token') ?? $request->input('token');

    // SI NO HAY TOKEN: No le demos error al usuario, mejor mandémoslo a mis-ordenes
    // donde podrá ver si el pago aparece como 'pagado' o 'pendiente'.
    if (!$token) {
        Log::warning('flowReturn sin token recibido.');
        return redirect()->route('patient.orders')
            ->with('info', 'Estamos procesando tu pago. Si no ves tu orden, espera unos segundos.');
    }

    // 3. Buscamos la orden.
    // IMPORTANTE: Asegúrate de que el modelo MedicalOrder tiene la columna 'flow_token'
    // o cámbialo por 'GatewayTransaction::where('token', $token)->first()->payable'
    $order = \App\Models\MedicalOrder::where('flow_token', $token)->first();

    if (!$order) {
        Log::error("Orden no encontrada para el token: $token");
        // Si no encontramos la orden, redirigimos a órdenes pero con un aviso
        return redirect()->route('patient.orders')
            ->with('error', 'No pudimos encontrar tu orden asociada al pago.');
    }

    // 4. Si todo está OK, redirección a éxito
    Log::info("Redirigiendo a éxito para orden ID: {$order->id}");
    return redirect()->route('payment.success', ['order' => $order->id]);
}




}
