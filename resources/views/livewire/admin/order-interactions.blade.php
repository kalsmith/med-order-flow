<div>
    <label class="text-muted small text-uppercase fw-bold mb-2">
        <i class="bi bi-chat-right-text me-1"></i> Antecedentes e Interacciones
    </label>

    {{-- Contenedor de Mensajes --}}
    <div class="interaction-container p-3 rounded bg-light border mb-3" id="chat-box" style="max-height: 300px; overflow-y: auto;">
        @forelse($order->interactions as $interaction)
            <div class="mb-3 d-flex flex-column {{ $interaction->sender_type === 'doctor' ? 'align-items-end' : 'align-items-start' }}">
                <div class="p-2 rounded shadow-sm {{ $interaction->sender_type === 'doctor' ? 'bg-primary text-white ms-5' : 'bg-white text-dark me-5 border' }}" style="max-width: 90%;">
                    <div class="d-flex justify-content-between gap-4 align-items-center mb-1">
                        <small class="fw-bold text-uppercase" style="font-size: 0.6rem;">
                            {{ $interaction->sender_type === 'doctor' ? 'Tú (Médico)' : 'Paciente' }}
                        </small>
                        <small class="opacity-75" style="font-size: 0.6rem;">
                            {{ $interaction->created_at->format('H:i') }}
                        </small>
                    </div>
                    <div style="font-size: 0.9rem; line-height: 1.4;">
                        {!! nl2br(e($interaction->content)) !!}
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-3 text-muted small italic">
                No hay mensajes previos.
            </div>
        @endforelse
    </div>

    {{-- Formulario Rápido de Consulta --}}
    <div class="input-group input-group-sm mt-2">
        <input type="text" wire:model="message" wire:keydown.enter="sendMessage"
               class="form-control border-primary"
               placeholder="Escribir consulta al paciente...">
        <button class="btn btn-primary" type="button" wire:click="sendMessage" wire:loading.attr="disabled">
            <span wire:loading.remove><i class="bi bi-send"></i></span>
            <span wire:loading class="spinner-border spinner-border-sm"></span>
        </button>
    </div>
    @error('message') <span class="text-danger tiny">{{ $message }}</span> @enderror
</div>
