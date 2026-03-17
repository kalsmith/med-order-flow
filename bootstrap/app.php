<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // 0. CONFIANZA EN PROXIES (Solución al "Doble Clic" / Login persistente)
        // Esto permite que Laravel detecte correctamente el HTTPS del servidor y asiente la cookie a la primera.
        $middleware->trustProxies(at: '*');

        // 1. REDIRECCIÓN INTELIGENTE
        $middleware->redirectGuestsTo(function (Request $request) {
            // Si es una ruta de administración o staff, login por formulario
            if ($request->is('gestion/*') || $request->is('admin/*') || $request->is('acceso')) {
                return route('login');
            }

            // Para pacientes, saltamos directo al login de Google
            // Laravel guardará la URL de destino en session('url.intended') automáticamente
            return route('auth.google');
        });

        // 2. EXCEPCIONES CSRF (Para Google Callback y Webhooks de Flow)
        $middleware->validateCsrfTokens(except: [
            'perfil/eliminar/solicitar',
            'perfil/eliminar/confirmar',
            'auth/google/callback',
            'payment/flow/*',
        ]);

        // 3. ALIAS DE MIDDLEWARES
        $middleware->alias([
            // Spatie (Roles y Permisos)
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

            // Perfil de Paciente Completo
            'check.profile' => \App\Http\Middleware\EnsurePatientProfileIsComplete::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
