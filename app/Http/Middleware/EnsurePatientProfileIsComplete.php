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

        // Si está logueado pero no tiene registro en la tabla 'patients'
        if ($user && !$user->patient) {

            /**
             * CAPTURA DE INTENCIÓN:
             * Intentamos obtener el ID del examen desde cualquier parámetro de la ruta.
             * Si tu ruta es /confirmar-pedido/{id}, esto capturará ese valor.
             */
            $examId = $request->route('id') ?? $request->route('pack') ?? $request->route('exam_type');

            if ($examId) {
                session(['pending_exam_id' => $examId]);
            }

            // Evitamos un bucle infinito: si ya va hacia la ruta de completar perfil, lo dejamos pasar
            if ($request->routeIs('profile.complete') || $request->routeIs('profile.update')) {
                return $next($request);
            }

            return redirect()->route('profile.complete')
                ->with('info', 'Para cumplir con la normativa de salud, necesitamos que completes tu perfil antes de emitir la orden.');
        }

        return $next($request);
    }
}
