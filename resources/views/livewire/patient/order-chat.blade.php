<div class="patient-chat-container position-relative" x-data="{ showNotice: false }">

    {{-- AVISO FLOTANTE --}}
    <div x-show="showNotice" x-transition class="position-absolute start-50 translate-middle-x z-3" style="bottom: 85px;">
        <button type="button" @click="scrollToBottom(); showNotice = false" class="btn btn-info btn-sm rounded-pill shadow-sm fw-bold px-3 border-0 text-white">
            <i class="bi bi-arrow-down me-1"></i> Nuevos mensajes
        </button>
    </div>

    {{-- ÁREA DE MENSAJES --}}
    <div class="chat-messages mb-3 p-3 shadow-inner"
         id="chat-box-{{ $order->id }}"
         wire:poll.5s="refreshMessages"
         style="height: 350px; overflow-y: auto; background: #fdfdfd; border-radius: 12px; scroll-behavior: smooth; border: 1px solid #eee;">

        @forelse($messages as $message)
            <div class="d-flex mb-3 {{ $message->sender_type === 'patient' ? 'justify-content-end' : 'justify-content-start' }}">
                <div class="max-width-75">
                    <div class="p-3 rounded-4 shadow-sm {{ $message->sender_type === 'patient' ? 'bg-primary text-white rounded-bottom-end-0' : 'bg-light text-dark rounded-bottom-start-0' }}">
                        @if($message->sender_type === 'doctor')
                            <small class="d-block fw-bold mb-1" style="font-size: 0.65rem; text-transform: uppercase; opacity: 0.8;">Médico Profesional</small>
                        @endif
                        <p class="mb-0 small">{{ $message->content }}</p>
                    </div>
                    <div class="small text-muted mt-1 {{ $message->sender_type === 'patient' ? 'text-end' : 'text-start' }}" style="font-size: 0.7rem;">
                        {{ $message->created_at->format('H:i') }}
                    </div>
                </div>
            </div>
        @empty
            {{-- Escenario: Sin mensajes aún --}}
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="bi bi- megaphone fs-1 text-primary opacity-25"></i>
                </div>
                <h6 class="fw-bold text-dark small">Su orden se está procesando automáticamente</h6>
                <p class="text-muted small px-4">Si el médico requiere antecedentes adicionales, se pondrá en contacto con usted por este medio.</p>
            </div>
        @endforelse
    </div>

    {{-- FORMULARIO DE INPUT: Condicionado --}}
    @if($canPatientReply)
        <form wire:submit.prevent="sendMessage" class="mt-2">
            <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white border border-primary">
                <input type="text"
                       wire:model="newMessage"
                       class="form-control border-0 px-4 py-2 shadow-none"
                       placeholder="Responder al médico..."
                       autocomplete="off">
                <button class="btn btn-primary px-4 border-0" type="submit">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </form>
    @else
        <div class="bg-light rounded-pill p-2 text-center border">
            <small class="text-muted">
                <i class="bi bi-lock-fill me-1"></i> El chat se activará si el médico le solicita información.
            </small>
        </div>
    @endif

    {{-- Scripts y Estilos se mantienen igual --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            const chatBox = document.getElementById('chat-box-{{ $order->id }}');
            const scrollToBottom = () => { if (chatBox) chatBox.scrollTop = chatBox.scrollHeight; };

            setTimeout(scrollToBottom, 100);
            @this.on('scroll-bottom', () => { setTimeout(scrollToBottom, 50); });
            @this.on('new-messages-received', () => {
                const distanceToBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight;
                if (distanceToBottom < 150) setTimeout(scrollToBottom, 50);
                else {
                    const alpine = document.querySelector('[x-data]').__x;
                    if(alpine) alpine.$data.showNotice = true;
                }
            });
        });
    </script>
</div>
