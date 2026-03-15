<div x-data="{ showNotice: false }">
    <label class="text-muted small text-uppercase fw-bold mb-2 d-flex justify-content-between align-items-center">
        <span><i class="bi bi-chat-right-text me-1"></i> Antecedentes e Interacciones</span>
        <span wire:loading class="spinner-border spinner-border-sm text-primary" style="--bs-spinner-width: 0.7rem; --bs-spinner-height: 0.7rem;"></span>
    </label>

    {{-- Contenedor de Mensajes: El polling se detiene si está firmado --}}
    <div class="interaction-container p-3 rounded bg-white border mb-2 position-relative"
         id="chat-box-admin"
         @if(!$readOnly) wire:poll.5s="refreshMessages" @endif
         style="height: 300px; overflow-y: auto; scroll-behavior: smooth; box-shadow: inset 0 2px 4px rgba(0,0,0,0.03);">

        {{-- Aviso flotante --}}
        <div x-show="showNotice"
             x-transition
             class="position-sticky start-50 translate-middle-x z-3"
             style="top: 10px; margin-bottom: -30px;">
            <button type="button" @click="scrollToBottom(); showNotice = false" class="btn btn-info btn-sm rounded-pill shadow fw-bold px-3 border-0 text-white" style="font-size: 0.7rem;">
                <i class="bi bi-arrow-down me-1"></i> El paciente respondió
            </button>
        </div>

        @forelse($interactions as $interaction)
            <div class="mb-3 d-flex flex-column {{ $interaction->sender_type === 'doctor' ? 'align-items-end' : 'align-items-start' }}">
                <div class="p-2 rounded shadow-sm {{ $interaction->sender_type === 'doctor' ? 'bg-primary text-white ms-5' : 'bg-light text-dark me-5 border' }}" style="max-width: 85%;">
                    <div class="d-flex justify-content-between gap-4 align-items-center mb-1">
                        <small class="fw-bold text-uppercase" style="font-size: 0.6rem; opacity: 0.8;">
                            {{ $interaction->sender_type === 'doctor' ? 'Tú (Médico)' : 'Paciente' }}
                        </small>
                        <small class="opacity-75" style="font-size: 0.6rem;">
                            {{ $interaction->created_at->format('H:i') }}
                        </small>
                    </div>
                    <div style="font-size: 0.85rem; line-height: 1.4;">
                        {!! nl2br(e($interaction->content)) !!}
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted small italic">
                <i class="bi bi-chat-left fs-2 d-block mb-2 opacity-25"></i>
                No hay mensajes previos.
            </div>
        @endforelse
    </div>

    {{-- Formulario: Se deshabilita o muestra mensaje de bloqueo --}}
    @if(!$readOnly)
        <div class="input-group input-group-sm">
            <input type="text"
                   wire:model="message"
                   wire:keydown.enter.prevent="sendMessage"
                   class="form-control border-primary px-3 py-2"
                   placeholder="Escribir consulta al paciente..."
                   autocomplete="off">
            <button class="btn btn-primary px-3" type="button" wire:click="sendMessage" wire:loading.attr="disabled">
                <i class="bi bi-send-fill" wire:loading.remove></i>
                <span wire:loading class="spinner-border spinner-border-sm"></span>
            </button>
        </div>
        @error('message') <span class="text-danger" style="font-size: 0.7rem;">{{ $message }}</span> @enderror
    @else
        <div class="bg-light p-2 rounded text-center border text-muted small">
            <i class="bi bi-lock-fill me-1"></i> El chat ha sido bloqueado porque la orden ya está firmada.
        </div>
    @endif

    {{-- Scripts... (Se mantienen igual) --}}
</div>
