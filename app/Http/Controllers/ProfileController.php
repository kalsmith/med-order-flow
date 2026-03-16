<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;

class ProfileController extends Controller
{

    public function showDeletePage()
    {
        return view('patient.profile.delete');
    }

    /**
     * Solicita el borrado de cuenta generando un código de verificación.
     */
    public function requestAccountDeletion(Request $request)
    {
        $user = auth()->user();

        // Generamos un código de 6 caracteres (mezcla letras y números)
        $code = strtoupper(Str::random(6));

        // Guardamos en la tabla de tokens de reset de contraseña para reusar infraestructura
        // Nota: En versiones recientes de Laravel la tabla es 'password_reset_tokens'
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($code),
                'created_at' => now()
            ]
        );

        // Aquí enviarías el correo. Puedes usar un closure rápido para pruebas:
        // Mail::raw("Tu código de confirmación para eliminar tu cuenta en MedOrder es: $code", function ($message) use ($user) {
        //     $message->to($user->email)->subject('Código de Verificación - Borrado de Cuenta');
        // });

        return back()->with('status', 'Hemos enviado un código de seguridad a su correo para confirmar el cierre de la cuenta.');
    }

    /**
     * Confirma el código y ejecuta el Soft Delete.
     */
    public function confirmAccountDeletion(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();
        $record = DB::table('password_reset_tokens')->where('email', $user->email)->first();

        if (!$record || !Hash::check(strtoupper($request->code), $record->token)) {
            return back()->withErrors(['code' => 'El código ingresado es incorrecto o ha expirado.']);
        }

        // 1. Renombrar correo para liberar el original (Ej: borrado_1710594321_cesar@gmail.com)
        $originalEmail = $user->email;
        $newEmail = 'deleted_' . time() . '_' . $originalEmail;

        // 2. Borrado lógico de pacientes asociados
        if (method_exists($user, 'patients')) {
            $user->patients()->delete();
        }

        // 3. Actualizar correo y ejecutar Soft Delete
        $user->update(['email' => $newEmail]);
        $user->delete();

        // 4. Limpiar token y cerrar sesión
        DB::table('password_reset_tokens')->where('email', $originalEmail)->delete();

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Su cuenta ha sido eliminada. El correo ' . $originalEmail . ' ha sido liberado.');
    }
}
