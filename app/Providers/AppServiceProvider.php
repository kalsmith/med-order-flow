<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Importante
use Illuminate\Pagination\Paginator; // <--- ESTA ES LA LÍNEA QUE FALTA O ESTÁ MAL

class AppServiceProvider extends ServiceProvider
{
    public function register(): void { }

    public function boot(): void
    {
        // Forzamos HTTPS en producción para que las cookies de sesión
        // se marquen como 'Secure' correctamente desde el inicio.
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }


        Paginator::useBootstrapFive();


    }
}
