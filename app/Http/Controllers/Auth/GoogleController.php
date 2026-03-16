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
        // Usamos stateless para evitar el error de "mismatch state"
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

        // Asignación de rol paciente si no es staff
        if (!$user->hasAnyRole(['admin', 'doctor', 'director_tecnico', 'contable'])) {
            if (!$user->hasRole('paciente')) {
                $user->assignRole('paciente');
            }
        }

        // LOGIN MANUAL
        Auth::login($user, true);

        // FORZAR PERSISTENCIA
        request()->session()->regenerate();
        session()->save(); // <--- AGREGA ESTA LÍNEA

        return redirect()->route('user.dispatch');

    } catch (\Exception $e) {
        return redirect()->route('login')->with('error', 'Error: ' . $e->getMessage());
    }
}


}
