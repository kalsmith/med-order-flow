<div class="patient-chat-container">

    {{-- ÁREA DE MENSAJES: Solo esto se refresca cada 10s de forma silenciosa --}}
    <div class="chat-messages mb-3 p-3 shadow-inner"
         id="chat-box-{{ $order->id }}"
         wire:poll.10s="refreshMessages"
         style="height: 350px; overflow-y: auto; background: #f8fafc; border-radius: 12px; scroll-behavior: smooth;">

        @foreach($order->interactions as $interaction)
            @php $isFromDoctor = ($interaction->sender_type === 'doctor'); @endphp

            <div class="d-flex {{ $isFromDoctor ? 'justify-content-start' : 'justify-content-end' }} mb-3">
                <div class="message-bubble {{ $isFromDoctor ? 'doctor-msg' : 'patient-msg' }} shadow-sm">
                    @if($isFromDoctor)
                        <span class="d-block fw-bold mb-1 text-primary" style="font-size: 0.65rem;">MÉDICO</span>
                    @endif

                    <p class="mb-0 text-break">{{ $interaction->content }}</p>

                    <small class="msg-time">{{ $interaction->created_at->format('H:i') }}</small>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ÁREA DE INPUT: Totalmente independiente del refresco automático --}}
    <form wire:submit.prevent="sendMessage" class="chat-input-wrapper">
        <div class="input-group shadow-sm border rounded-pill overflow-hidden bg-white">
            <input type="text"
                   wire:model.defer="newMessage"
                   class="form-control border-0 px-4 py-2"
                   placeholder="Escribe tu respuesta al médico..."
                   style="box-shadow: none;">

            {{-- wire:target asegura que el botón solo muestre carga al enviar mensaje --}}
            <button class="btn btn-primary px-4"
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="sendMessage">

                <i class="bi bi-send-fill" wire:loading.remove wire:target="sendMessage"></i>

                <div class="spinner-border spinner-border-sm"
                     role="status"
                     wire:loading
                     wire:target="sendMessage"></div>
            </button>
        </div>
    </form>

<script>
    document.addEventListener('livewire:load', function () {
        const chatBoxId = 'chat-box-{{ $order->id }}';

        const scrollToBottom = () => {
            const chatBox = document.getElementById(chatBoxId);
            if (chatBox) {
                chatBox.scrollTo({
                    top: chatBox.scrollHeight,
                    behavior: 'smooth'
                });
            }
        };

        // 1. Ejecutar al cargar
        scrollToBottom();

        // 2. Observer: Detecta cuando aparecen nuevos elementos (mensajes) en el chat
        const targetNode = document.getElementById(chatBoxId);
        if (targetNode) {
            const observer = new MutationObserver(() => {
                scrollToBottom();
            });
            observer.observe(targetNode, { childList: true });
        }

        // 3. Por si acaso, escuchar el evento manual desde PHP
        window.addEventListener('scroll-bottom', scrollToBottom);

        // 4. Especial para Livewire Poll: ejecutar después de cada actualización
        Livewire.hook('message.processed', (message, component) => {
            if (component.fingerprint.name === 'patient.order-chat') {
                scrollToBottom();
            }
        });
    });
</script>
</div>
