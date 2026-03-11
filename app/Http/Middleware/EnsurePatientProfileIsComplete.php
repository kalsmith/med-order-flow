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

        // 1. Si no hay usuario o es Staff, no hacemos nada (pasa al siguiente middleware)
        if (!$user || $user->hasAnyRole(['doctor', 'admin', 'director_tecnico', 'contable'])) {
            return $next($request);
        }

        // 2. Si ya tiene perfil, adelante
        $hasProfile = $user->patients()->where('relationship', 'self')->exists();
        if ($hasProfile) {
            return $next($request);
        }

        // 3. Si está en proceso de completar perfil, no lo interrumpimos (evita bucle)
        if ($request->routeIs('profile.complete') || $request->routeIs('profile.store')) {
            return $next($request);
        }

        // 4. Si llegó aquí es un Paciente sin RUT: a completar perfil.
        // Laravel ya guardó la URL actual en la sesión como "intended"
        // porque este middleware corre después de 'auth'.
        return redirect()->route('profile.complete')
            ->with('info', 'Por favor, completa tus datos legales para poder emitir órdenes médicas.');
    }
}
