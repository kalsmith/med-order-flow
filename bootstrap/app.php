<?php

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

        // Excluimos todo el prefijo de pagos para asegurar que Flow
        // pueda enviar sus Webhooks sin recibir un error de token CSRF
        $middleware->validateCsrfTokens(except: [
            'payment/flow/*',
        ]);

        // Redirección inteligente: los pacientes van a Google, los admins al Login
        $middleware->redirectGuestsTo(function (Request $request) {

            // Si el usuario intenta entrar a rutas de pacientes sin estar logueado,
            // forzamos el flujo de Google en lugar del login tradicional.
            if ($request->is('confirmar-pedido/*') || $request->is('mis-ordenes')) {
                return route('auth.google');
            }

            // Por defecto, mandamos al login de toda la vida (admin/staff)
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
