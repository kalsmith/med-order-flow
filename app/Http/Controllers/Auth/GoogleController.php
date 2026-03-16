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
        // stateless() es OBLIGATORIO cuando usas SameSite=none para evitar errores de state
        $googleUser = Socialite::driver('google')->stateless()->user();

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
        }

        // Asignar rol si no es staff
        if (!$user->hasAnyRole(['admin', 'doctor', 'director_tecnico', 'contable'])) {
            if (!$user->hasRole('paciente')) {
                $user->assignRole('paciente');
            }
        }

        // --- LOGIN Y PERSISTENCIA ---
        Auth::login($user, true);

        // Forzamos la regeneración y el guardado inmediato
        request()->session()->regenerate();
        request()->session()->save();

        return redirect()->route('user.dispatch');

    } catch (\Exception $e) {
        // Si falla, regrésalo a la home con el error para saber qué pasó
        return redirect('/')->with('error', 'Error Google: ' . $e->getMessage());
    }
}


}
