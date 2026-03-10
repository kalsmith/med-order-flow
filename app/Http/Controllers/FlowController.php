<?php

namespace App\Http\Controllers;

use App\Services\FlowService;
use Illuminate\Http\Request;

class FlowController extends Controller
{
    public function confirmation(Request $request)
    {
        $token = $request->input('token');
        // Usamos el método que ya tenías bien construido en el Service
        app(FlowService::class)->handleWebhook($token);

        return response()->json(['status' => 'ok']);
    }

    public function returnUrl(Request $request)
    {
        $token = $request->query('token');
        // Si no hay token, fuera
        if (!$token) return redirect()->route('home');

        // Aquí podrías redirigir al éxito
        return redirect()->route('payment.success', ['token' => $token]);
    }
}
