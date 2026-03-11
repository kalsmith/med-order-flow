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

    // --- CLÁUSULA DE ESCAPE PARA STAFF ---
    // Si el usuario es Doctor, Admin o Director Técnico, NO verificamos perfil de paciente.
    if ($user && $user->hasAnyRole(['doctor', 'admin', 'director_tecnico'])) {
        return $next($request);
    }

    $hasProfile = $user && $user->patients()->where('relationship', 'self')->exists();

    if ($user && !$hasProfile) {
        // Evitamos bucle infinito en las rutas de completar perfil
        if ($request->routeIs('profile.complete') || $request->routeIs('profile.store')) {
            return $next($request);
        }

        // 1. Captura de intención para Exámenes Estándar
        $examId = $request->route('id') ?? $request->route('pack') ?? $request->route('exam_type');
        if ($examId) {
            $idToStore = is_object($examId) ? $examId->id : $examId;
            session(['pending_exam_id' => $idToStore]);
        }

        // 2. Captura de intención para Orden Personalizada
        if ($request->routeIs('orders.custom')) {
            session(['pending_custom_order' => true]);
        }

        return redirect()->route('profile.complete')
            ->with('info', 'Para cumplir con la normativa de salud, necesitamos que completes tu perfil antes de emitir la orden.');
    }

    return $next($request);
}
}
