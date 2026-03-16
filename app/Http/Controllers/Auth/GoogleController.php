<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $googleUser = Socialite::driver('google')->user();

        // 1. Buscar por Google ID o por Email (por si ya se registró manualmente antes)
        $user = User::where('google_id', $googleUser->id)
                    ->orWhere('email', $googleUser->email)
                    ->first();

        if (!$user) {
            // 2. Crear usuario nuevo
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'password' => Hash::make(Str::random(24)),
                'email_verified_at' => now(),
            ]);
        } else {
            // 3. Si el usuario existía pero no tenía vinculado Google, lo vinculamos
            if (!$user->google_id) {
                $user->update([
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                ]);
            }
        }

        // --- BLINDAJE DE ROLES ---
        // Si no tiene ningún rol administrativo/staff, nos aseguramos de que sea 'paciente'
        if (!$user->hasAnyRole(['admin', 'doctor', 'director_tecnico', 'contable'])) {

            // Verificación de emergencia: Si el rol no existe en la tabla 'roles', lo creamos
            if (!\Spatie\Permission\Models\Role::where('name', 'paciente')->exists()) {
                \Spatie\Permission\Models\Role::create(['name' => 'paciente']);
            }

            // Asignamos el rol si no lo tiene (evita el error 403 en las rutas protegidas)
            if (!$user->hasRole('paciente')) {
                $user->assignRole('paciente');
            }
        }

        Auth::login($user);

        // 4. PERSISTENCIA DEL INTENTO (URL INTENDED)
        $intendedUrl = session()->pull('url.intended', route('user.dispatch'));

        // Limpieza de redirección para evitar bucles en el login
        if ($intendedUrl == url('/') || Str::contains($intendedUrl, 'auth/google')) {
            return redirect()->route('user.dispatch');
        }

        return redirect()->to($intendedUrl)
                         ->with('success', 'Sesión iniciada correctamente.');

    } catch (Exception $e) {
        return redirect()->route('home')
                         ->with('error', 'Error al autenticar: ' . $e->getMessage());
    }
}


}
