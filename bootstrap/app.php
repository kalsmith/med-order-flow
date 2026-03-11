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

        // 1. REDIRECCIÓN INTELIGENTE (Paso 1 de tu esquema: ¿Está logueado?)
        $middleware->redirectGuestsTo(function (Request $request) {
            // Si es una ruta de administración o staff, login por formulario
            if ($request->is('gestion/*') || $request->is('admin/*') || $request->is('acceso')) {
                return route('login');
            }

            // Para pacientes (Ruta A, B, C...), saltamos directo al login de Google
            // Laravel guardará la URL de destino en session('url.intended') automáticamente
            return route('auth.google');
        });

        // 2. EXCEPCIONES CSRF (Para que Flow pueda avisarnos de los pagos)
        $middleware->validateCsrfTokens(except: [
            'auth/google/callback',
            'payment/flow/*',
        ]);

        // 3. ALIAS DE MIDDLEWARES
        $middleware->alias([
            // Spatie (Roles y Permisos)
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

            // PASO 2 de tu esquema: ¿Tiene registro local en User? (Patient Profile)
            'check.profile' => \App\Http\Middleware\EnsurePatientProfileIsComplete::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
