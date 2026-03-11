<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePatientProfileIsComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        $hasProfile = $user && $user->patients()->where('relationship', 'self')->exists();

        if ($user && !$hasProfile) {

            // 1. Captura de intención para Exámenes Estándar
            $examId = $request->route('id') ?? $request->route('pack') ?? $request->route('exam_type');
            if ($examId) {
                $idToStore = is_object($examId) ? $examId->id : $examId;
                session(['pending_exam_id' => $idToStore]);
            }

            // 2. NUEVO: Captura de intención para Orden Personalizada
            // Si el usuario viene de la ruta 'orders.custom', guardamos una marca en sesión
            if ($request->routeIs('orders.custom')) {
                session(['pending_custom_order' => true]);
            }

            // Evitamos bucle infinito
            if ($request->routeIs('profile.complete') || $request->routeIs('profile.store')) {
                return $next($request);
            }

            return redirect()->route('profile.complete')
                ->with('info', 'Para cumplir con la normativa de salud, necesitamos que completes tu perfil antes de emitir la orden.');
        }

        return $next($request);
    }
}
