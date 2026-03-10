<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    /**
     * Redirige al usuario a la página de autenticación de Google.
     */
    public function redirectToGoogle()
    {
        // Agregamos ->with(['prompt' => 'select_account'])
        // Esto obliga a Google a mostrar siempre el selector de cuentas.
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Obtiene la información del usuario de Google.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // 1. Buscamos si el usuario ya existe por su Google ID o por su Email
            $user = User::where('google_id', $googleUser->id)
                        ->orWhere('email', $googleUser->email)
                        ->first();

            if (!$user) {
                // 2. Si no existe, lo creamos
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'password' => Hash::make(Str::random(24)), // Password aleatorio por seguridad
                    'email_verified_at' => now(),
                ]);

                // Asignamos el rol de paciente por defecto (Spatie Roles)
                $user->assignRole('paciente');
            } else {
                // 3. Si existía pero no tenía Google ID (ej: se registró manual antes), lo actualizamos
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar,
                    ]);
                }
            }

            // 4. Iniciamos sesión
            Auth::login($user);

            /**
             * 5. REDIRECCIÓN INTELIGENTE
             * redirect()->intended('/') intentará enviar al usuario a la URL que quería visitar
             * antes de ser interceptado por el login (ej: /confirmar-pedido/7).
             * Si no hay ninguna URL previa, lo mandará a la Home.
             */
            return redirect()->intended(route('home'))
                             ->with('success', 'Sesión iniciada correctamente.');

        } catch (Exception $e) {
            return redirect()->route('home')
                             ->with('error', 'Hubo un error al autenticar con Google: ' . $e->getMessage());
        }
    }
}
