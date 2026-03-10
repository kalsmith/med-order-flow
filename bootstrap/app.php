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

    $middleware->validateCsrfTokens(except: [
        'payment/flow/return',
        'payment/flow/confirmation',
    ]);


        // REDIRECCIÓN INTELIGENTE SEGÚN LA RUTA
        $middleware->redirectGuestsTo(function (Request $request) {

            // Si el usuario intenta ir a rutas de pacientes (comprar o ver órdenes)
            // lo mandamos DIRECTO al flujo de Google sin ver el login de admin.
            if ($request->is('confirmar-pedido/*') || $request->is('mis-ordenes')) {
                return route('auth.google');
            }

            // Para cualquier otra ruta (como entrar a /admin),
            // usamos el login tradicional de toda la vida.
            return route('login');
        });

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'check.profile' => \App\Http\Middleware\EnsurePatientProfileIsComplete::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
