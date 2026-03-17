<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class GoogleController extends Controller
{
    /**
     * Redirige al usuario a la página de autenticación de Google.
     */
public function redirectToGoogle()
{
    // Si ya viene de una ruta protegida por middleware 'auth',
    // Laravel ya guardó la URL en session('url.intended').
    // Solo forzamos si no existe, para no pisar la redirección real.
    if (!session()->has('url.intended')) {
        session(['url.intended' => url()->previous()]);
    }

return Socialite::driver('google')
        // ESTO ES LA CLAVE:
        ->with(['prompt' => 'select_account'])
        ->redirect();
}

    /**
     * Obtiene la información del usuario de Google.
     */


public function handleGoogleCallback()
{
    try {
        Log::info('--- INICIO CALLBACK GOOGLE ---');

        // Usamos stateless() para evitar el error de mismatch de estado que causa el loop
        $googleUser = Socialite::driver('google')->stateless()->user();
        Log::info('Usuario obtenido de Google: ' . $googleUser->email);

        $user = User::where('google_id', $googleUser->id)
                    ->orWhere('email', $googleUser->email)
                    ->first();

        if (!$user) {
            Log::info('Creando nuevo usuario');
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'password' => Hash::make(Str::random(24)),
                'email_verified_at' => now(),
            ]);
        }

        // Blindaje de roles
        if (!$user->hasAnyRole(['admin', 'doctor', 'director_tecnico', 'contable'])) {
            if (!$user->hasRole('paciente')) {
                $user->assignRole('paciente');
                Log::info('Rol paciente asignado');
            }
        }

        Log::info('Intentando Auth::login para ID: ' . $user->id);
        Auth::login($user, true);

        // PERSISTENCIA CRÍTICA
        request()->session()->regenerate();
        session()->save();

        Log::info('Sesión regenerada y guardada. ID Sesión: ' . session()->getId());

        $intendedUrl = session()->pull('url.intended', route('user.dispatch'));
        Log::info('Redirigiendo a: ' . $intendedUrl);

        return redirect()->to($intendedUrl);

    } catch (Exception $e) {
        Log::error('ERROR EN GOOGLE CALLBACK: ' . $e->getMessage());
        return redirect()->route('home')->with('error', 'Error crítico: ' . $e->getMessage());
    }
}


}
