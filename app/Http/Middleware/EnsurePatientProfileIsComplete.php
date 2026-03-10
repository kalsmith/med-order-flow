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

        /**
         * CAMBIO CLAVE:
         * Ahora verificamos si existe al menos un paciente asociado
         * que tenga la relación 'self' (el titular).
         */
        $hasProfile = $user && $user->patients()->where('relationship', 'self')->exists();

        if ($user && !$hasProfile) {

            // Captura de intención para no perder el examen que el usuario quería comprar
            $examId = $request->route('id') ?? $request->route('pack') ?? $request->route('exam_type');

            if ($examId) {
                // Si el examId es un objeto de modelo (Route Model Binding), guardamos solo el ID
                $idToStore = is_object($examId) ? $examId->id : $examId;
                session(['pending_exam_id' => $idToStore]);
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
