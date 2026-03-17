<div class="card shadow border-0" style="border-radius: 20px;">
    <div class="card-body p-4 text-center">

        @if($step == 1)
            <i class="bi bi-shield-exclamation text-warning mb-3" style="font-size: 3.5rem;"></i>
            <h3 class="fw-bold">Eliminar Cuenta</h3>
            <p class="text-muted px-3">Enviaremos un código de seguridad a tu correo para confirmar.</p>

            <button wire:click="requestVerificationCode" wire:loading.attr="disabled" class="btn btn-primary rounded-pill w-100 fw-bold py-3">
                <span wire:loading.remove>Enviar código al correo</span>
                <span wire:loading><span class="spinner-border spinner-border-sm"></span> Enviando...</span>
            </button>
            @error('email') <div class="text-danger mt-2 small">{{ $message }}</div> @enderror

        @else
            <i class="bi bi-envelope-check text-success mb-3" style="font-size: 3.5rem;"></i>
            <h3 class="fw-bold">Verifica tu correo</h3>
            <p class="text-muted small">Ingresa el código enviado a <b>{{ auth()->user()->email }}</b></p>

            <div class="mb-4 mt-4">
                <input type="text" wire:model="code" class="form-control form-control-lg text-center fw-bold"
                       placeholder="000000" maxlength="6" style="letter-spacing: 8px; font-size: 2rem; border-radius: 15px;">
                @error('code') <div class="text-danger mt-2 small">{{ $message }}</div> @enderror
            </div>

            <button wire:click="confirmDeletion" wire:loading.attr="disabled" class="btn btn-danger rounded-pill w-100 fw-bold py-3">
                <span wire:loading.remove>Confirmar eliminación definitiva</span>
                <span wire:loading><span class="spinner-border spinner-border-sm"></span> Procesando...</span>
            </button>

            <button wire:click="$set('step', 1)" class="btn btn-link btn-sm mt-3 text-muted">Volver atrás</button>
        @endif

    </div>
</div>
