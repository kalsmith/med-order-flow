<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
        Mail::raw("Tu código de seguridad para eliminar tu cuenta en PideTuExamen es: $code. Si no solicitaste esto, ignora este mensaje.", function ($message) use ($user) {
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
        $record = DB::table('password_reset_tokens')->where('email', $user->email)->first();

        // Validar si el código existe y es correcto
        if (!$record || !Hash::check($request->code, $record->token)) {
            return back()->withErrors(['code' => 'El código ingresado es incorrecto o ha expirado.']);
        }

        $originalEmail = $user->email;

        // MARCA VISIBLE: DEL_TIMESTAMP_EMAIL
        $newEmail = 'DEL_' . time() . '_' . $originalEmail;

        DB::beginTransaction();
        try {
            // 1. Borrado lógico de pacientes asociados (si existe la relación)
            if (method_exists($user, 'patients')) {
                $user->patients()->delete();
            }

            // 2. Renombrar y Soft Delete del usuario
            $user->email = $newEmail;
            $user->save();
            $user->delete();

            // 3. Limpiar token
            DB::table('password_reset_tokens')->where('email', $originalEmail)->delete();

            DB::commit();

            // 4. Logout y limpieza de sesión
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/')->with('success', 'Cuenta eliminada con éxito. El correo ' . $originalEmail . ' ha sido liberado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['code' => 'Error al procesar la eliminación: ' . $e->getMessage()]);
        }
    }
}
