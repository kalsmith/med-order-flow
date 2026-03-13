<div class="patient-chat-container position-relative" x-data="{ showNotice: false }">

    {{-- AVISO FLOTANTE --}}
    <div x-show="showNotice"
         x-transition
         class="position-absolute start-50 translate-middle-x z-3"
         style="bottom: 85px;">
        <button type="button"
                @click="scrollToBottom(); showNotice = false"
                class="btn btn-info btn-sm rounded-pill shadow-sm fw-bold px-3 border-0 text-white">
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
                        <p class="mb-0 small">{{ $message->content }}</p>
                    </div>
                    <div class="small text-muted mt-1 {{ $message->sender_type === 'patient' ? 'text-end' : 'text-start' }}" style="font-size: 0.7rem;">
                        {{ $message->created_at->format('H:i') }}
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="bi bi-chat-dots fs-2 opacity-25"></i>
                <p class="small mt-2">Inicia la conversación con el médico profesional.</p>
            </div>
        @endforelse
    </div>

    {{-- FORMULARIO DE INPUT --}}
    <form wire:submit.prevent="sendMessage" class="mt-2">
        <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white border">
            <input type="text"
                   wire:model="newMessage"
                   class="form-control border-0 px-4 py-2 shadow-none"
                   placeholder="Escribe un mensaje..."
                   autocomplete="off">
            <button class="btn btn-primary px-4 border-0" type="submit">
                <i class="bi bi-send-fill"></i>
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('livewire:initialized', () => {
            const chatBox = document.getElementById('chat-box-{{ $order->id }}');

            const scrollToBottom = () => {
                if (chatBox) {
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            };

            // Ejecutar al cargar
            setTimeout(scrollToBottom, 100);

            // Escuchar eventos de Livewire
            @this.on('scroll-bottom', () => {
                setTimeout(scrollToBottom, 50);
            });

            @this.on('new-messages-received', () => {
                const distanceToBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight;
                if (distanceToBottom < 150) {
                    setTimeout(scrollToBottom, 50);
                } else {
                    // Acceder a Alpine de forma segura
                    const alpine = document.querySelector('[x-data]').__x;
                    if(alpine) alpine.$data.showNotice = true;
                }
            });
        });
    </script>

    <style>
        .max-width-75 { max-width: 75%; }
        .rounded-bottom-end-0 { border-bottom-right-radius: 0 !important; }
        .rounded-bottom-start-0 { border-bottom-left-radius: 0 !important; }
        .shadow-inner { box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05); }
    </style>
</div>
