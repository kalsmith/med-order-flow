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
    // Si ya viene de una ruta protegida por middleware 'auth',
    // Laravel ya guardó la URL en session('url.intended').
    // Solo forzamos si no existe, para no pisar la redirección real.
    if (!session()->has('url.intended')) {
        session(['url.intended' => url()->previous()]);
    }

    return Socialite::driver('google')
        // ELIMINAMOS 'select_account' para que si ya está logueado en Google, pase directo.
        // Si el usuario quiere usar otra cuenta, Google igual ofrece la opción si no hay sesión.
        ->redirect();
}

    /**
     * Obtiene la información del usuario de Google.
     */

    public function handleGoogleCallback()
    {
        try {
            // Usamos stateless() para evitar errores de mismatch de estado en algunos navegadores
            $googleUser = Socialite::driver('google')->stateless()->user();

            // 1. Buscar o vincular usuario
            $user = User::where('google_id', $googleUser->id)
                        ->orWhere('email', $googleUser->email)
                        ->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => now(),
                ]);
            } else {
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar,
                    ]);
                }
            }

            // 2. Blindaje de roles
            if (!$user->hasAnyRole(['admin', 'doctor', 'director_tecnico', 'contable'])) {
                if (!\Spatie\Permission\Models\Role::where('name', 'paciente')->exists()) {
                    \Spatie\Permission\Models\Role::create(['name' => 'paciente']);
                }
                if (!$user->hasRole('paciente')) {
                    $user->assignRole('paciente');
                }
            }

            // 3. LOGIN Y REGENERACIÓN (Vital para romper el bucle)
            Auth::login($user, true);
            request()->session()->regenerate();

            // 4. Redirección limpia
            $intendedUrl = session()->pull('url.intended');

            // Si no hay URL previa o es una ruta de auth, vamos al dispatch
            if (!$intendedUrl || Str::contains($intendedUrl, 'auth') || $intendedUrl == url('/')) {
                return redirect()->route('user.dispatch');
            }

            return redirect()->to($intendedUrl);

        } catch (\Exception $e) {
            return redirect()->route('login')
                            ->with('error', 'Error al autenticar: ' . $e->getMessage());
        }
    }


}
