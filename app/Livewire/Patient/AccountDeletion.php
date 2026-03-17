<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use Illuminate\Support\Facades\{Hash, Mail, DB, Auth, Log};
use Illuminate\Support\Str;

class AccountDeletion extends Component
{
    public $step = 1; // 1: Inicial, 2: Código
    public $code;

    /**
     * Paso 1: Generar código y enviar por SMTP
     */
    public function requestVerificationCode()
    {
        $user = auth()->user();
        $generatedCode = rand(100000, 999999);

        Log::info("--- SOLICITUD DE BORRADO DE CUENTA ---", [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        try {
            // 1. Guardar/Actualizar el token en la tabla
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($generatedCode),
                    'created_at' => now()
                ]
            );
            Log::info("Token de seguridad generado y guardado en DB.");

            // 2. Enviar el correo usando la cuenta que sí funciona (no-reply@soltys.cl)
            Mail::raw("Tu código de seguridad para eliminar tu cuenta en PideTuExamen es: $generatedCode. Si no solicitaste esto, ignora este mensaje.", function ($message) use ($user, $generatedCode) {
                $message->to($user->email)
                        ->from('no-reply@soltys.cl', 'PideTuExamen')
                        ->subject($generatedCode . " es tu código de verificación");
            });

            Log::info("Correo enviado exitosamente a: " . $user->email);

            $this->step = 2;
            session()->flash('message', 'Código enviado exitosamente.');

        } catch (\Exception $e) {
            Log::error("ERROR CRÍTICO EN SOLICITUD DE BORRADO", [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            $this->addError('email', 'Error al enviar el correo. Por favor, intenta más tarde.');
        }
    }

    /**
     * Paso 2: Validar código y ejecutar borrado lógico (Soft Delete + Renombrado)
     */
    public function confirmDeletion()
    {
        $this->validate([
            'code' => 'required|digits:6'
        ]);

        $user = auth()->user();
        $originalEmail = $user->email;
        $userId = $user->id;

        Log::info("--- PROCESANDO CONFIRMACIÓN DE BORRADO ---", ['user_id' => $userId]);

        $record = DB::table('password_reset_tokens')->where('email', $originalEmail)->first();

        // Validar el código
        if (!$record || !Hash::check($this->code, $record->token)) {
            Log::warning("Código de borrado incorrecto", ['user_id' => $userId, 'code_entered' => $this->code]);
            $this->addError('code', 'El código es incorrecto o ha expirado.');
            return;
        }

        // Definir nueva marca de correo
        $newEmail = 'DEL_' . time() . '_' . $originalEmail;

        DB::beginTransaction();
        try {
            // 1. Borrado de pacientes asociados
            if (method_exists($user, 'patients')) {
                $count = $user->patients()->count();
                $user->patients()->delete();
                Log::info("Pacientes asociados borrados (SoftDelete)", ['count' => $count]);
            }

            // 2. Renombrar correo y aplicar SoftDelete al usuario
            $user->email = $newEmail;
            $user->save();
            $user->delete();
            Log::info("Usuario renombrado a $newEmail y desactivado (SoftDelete).");

            // 3. Limpiar token usado
            DB::table('password_reset_tokens')->where('email', $originalEmail)->delete();
            Log::info("Token de seguridad limpiado.");

            DB::commit();

            Log::info("--- ELIMINACIÓN DE CUENTA FINALIZADA CON ÉXITO ---", ['user_id' => $userId]);

            // 4. Logout y destrucción de sesión
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            return redirect()->to('/')->with('success', 'Tu cuenta ha sido eliminada. El correo ' . $originalEmail . ' ha sido liberado.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("ERROR DURANTE LA EJECUCIÓN DEL BORRADO", [
                'user_id' => $userId,
                'message' => $e->getMessage()
            ]);
            $this->addError('code', 'Hubo un problema al procesar la solicitud.');
        }
    }

    public function render()
    {
        return view('livewire.patient.account-deletion');
    }
}
