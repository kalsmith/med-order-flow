<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Paso 1: Mostrar la vista de confirmación inicial
     */
    public function showDeletePage()
    {
        return view('patient.profile.delete-confirm');
    }

    /**
     * Paso 2: Generar código y enviar Mail
     */
    public function requestAccountDeletion(Request $request)
    {
        $user = auth()->user();

        // Generamos un código de 6 dígitos numéricos (más fácil para el usuario)
        $code = rand(100000, 999999);

        // Guardamos en password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($code),
                'created_at' => now()
            ]
        );

        // Enviar el correo (Usando raw para probar rápido, luego puedes hacer un Mailable)
    // Busca la parte del Mail::raw y déjala así:
    Mail::raw("Tu código de seguridad para eliminar tu cuenta en PideTuExamen es: $code. Si no solicitaste esto, ignora este mensaje.", function ($message) use ($user, $code) { // <--- AGREGAR $code AQUÍ
        $message->to($user->email)->subject($code . ' es tu código de verificación');
    });

        return back()->with('status', 'code_sent');
    }

    /**
     * Paso 3: Validar código y ejecutar Soft Delete con renombrado
     */


public function confirmAccountDeletion(Request $request)
{
    $request->validate([
        'code' => 'required|string|size:6',
    ]);

    $user = auth()->user();
    $originalEmail = $user->email;
    $userId = $user->id;

    Log::info("--- INICIO ELIMINACIÓN DE CUENTA ---", ['user_id' => $userId, 'email' => $originalEmail]);

    $record = DB::table('password_reset_tokens')->where('email', $originalEmail)->first();

    // Validar si el código existe y es correcto
    if (!$record || !Hash::check($request->code, $record->token)) {
        Log::warning("Intento de eliminación fallido: Código incorrecto o expirado", [
            'user_id' => $userId,
            'input_code' => $request->code
        ]);
        return back()->withErrors(['code' => 'El código ingresado es incorrecto o ha expirado.']);
    }

    // MARCA VISIBLE: DEL_TIMESTAMP_EMAIL
    $newEmail = 'DEL_' . time() . '_' . $originalEmail;

    DB::beginTransaction();
    try {
        // 1. Borrado lógico de pacientes asociados
        if (method_exists($user, 'patients')) {
            $count = $user->patients()->count();
            $user->patients()->delete();
            Log::info("Pacientes asociados eliminados (SoftDelete)", ['cantidad' => $count]);
        }

        // 2. Renombrar y Soft Delete del usuario
        $user->email = $newEmail;
        $user->save();
        $user->delete();
        Log::info("Usuario renombrado y desactivado", ['nuevo_email' => $newEmail]);

        // 3. Limpiar token
        DB::table('password_reset_tokens')->where('email', $originalEmail)->delete();
        Log::info("Token de seguridad eliminado");

        DB::commit();

        // 4. Logout y limpieza de sesión
        Log::info("Cerrando sesión del usuario eliminado");
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info("--- ELIMINACIÓN COMPLETADA EXITOSAMENTE ---");

        return redirect('/')->with('success', 'Cuenta eliminada con éxito. El correo ' . $originalEmail . ' ha sido liberado.');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("ERROR CRÍTICO EN ELIMINACIÓN DE CUENTA", [
            'user_id' => $userId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return back()->withErrors(['code' => 'Error al procesar la eliminación: ' . $e->getMessage()]);
    }
}


}
