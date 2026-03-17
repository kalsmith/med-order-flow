<?php

namespace App\Livewire\Patient;

use Livewire\Component;
use Illuminate\Support\Facades\{Hash, Mail, DB, Auth, Log};
use Illuminate\Support\Str;

class AccountDeletion extends Component
{
    public $step = 1; // 1: Inicial, 2: Código
    public $code;
    public $loading = false;

    public function requestVerificationCode()
    {
        $user = auth()->user();
        $generatedCode = rand(100000, 999999);

        // Guardar código
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($generatedCode), 'created_at' => now()]
        );

        // Enviar Mail (Usando los datos de tu .env)
        try {
            Mail::raw("Tu código de seguridad es: $generatedCode", function ($message) use ($user, $generatedCode) {
                $message->to($user->email)->subject($generatedCode . " es tu código de verificación");
            });

            $this->step = 2;
            session()->flash('message', 'Código enviado exitosamente.');
        } catch (\Exception $e) {
            Log::error("Error enviando mail: " . $e->getMessage());
            $this->addError('email', 'No pudimos enviar el correo. Revisa tu configuración SMTP.');
        }
    }

    public function confirmDeletion()
    {
        $this->validate(['code' => 'required|digits:6']);

        $user = auth()->user();
        $record = DB::table('password_reset_tokens')->where('email', $user->email)->first();

        if (!$record || !Hash::check($this->code, $record->token)) {
            $this->addError('code', 'El código es incorrecto.');
            return;
        }

        // --- LÓGICA DE BORRADO ---
        $originalEmail = $user->email;
        $newEmail = 'DEL_' . time() . '_' . $originalEmail;

        DB::transaction(function () use ($user, $newEmail, $originalEmail) {
            if (method_exists($user, 'patients')) { $user->patients()->delete(); }
            $user->update(['email' => $newEmail]);
            $user->delete();
            DB::table('password_reset_tokens')->where('email', $originalEmail)->delete();
        });

        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->to('/')->with('success', 'Cuenta eliminada correctamente.');
    }

    public function render()
    {
        return view('livewire.patient.account-deletion');
    }
}
