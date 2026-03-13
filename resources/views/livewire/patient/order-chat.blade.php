<div class="patient-chat-container position-relative" x-data="{ showNotice: false }">

    {{-- AVISO FLOTANTE --}}
    <div x-show="showNotice"
         x-transition
         class="position-absolute start-50 translate-middle-x z-3"
         style="bottom: 80px;">
        <button type="button"
                @click="scrollToBottom(); showNotice = false"
                class="btn btn-info btn-sm rounded-pill shadow fw-bold px-3 py-2 border-0 text-white"
                style="background: #0dcaf0;">
            <i class="bi bi-arrow-down me-1"></i> Hay nuevos mensajes
        </button>
    </div>

    {{-- ÁREA DE MENSAJES --}}
    <div class="chat-messages mb-3 p-3 shadow-inner"
         id="chat-box-{{ $order->id }}"
         wire:poll.10s="refreshMessages"
         @scroll="checkScroll()"
         style="height: 350px; overflow-y: auto; background: #f8fafc; border-radius: 12px; scroll-behavior: smooth;">

        @foreach($order->interactions as $interaction)
            {{-- ... tu bucle de mensajes igual ... --}}
        @endforeach
    </div>

    {{-- FORMULARIO DE INPUT (IGUAL) --}}
    <form wire:submit.prevent="sendMessage" ...> ... </form>

    <script>
        document.addEventListener('livewire:load', function () {
            const chatBoxId = 'chat-box-{{ $order->id }}';
            const chatBox = document.getElementById(chatBoxId);

            window.scrollToBottom = () => {
                if (chatBox) {
                    chatBox.scrollTo({ top: chatBox.scrollHeight, behavior: 'smooth' });
                }
            };

            // Detectar si el usuario está al fondo o no
            window.checkScroll = () => {
                const distanceToBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight;
                // Si el usuario sube más de 100px, dejamos de bajar automáticamente
                if (distanceToBottom < 100) {
                    // Si está al fondo, ocultamos el aviso
                    // Usamos el scope de Alpine si fuera necesario, pero aquí lo manejamos con eventos
                }
            };

            // 1. Si el usuario ENVÍA un mensaje, bajamos SIEMPRE
            window.addEventListener('scroll-bottom', scrollToBottom);

            // 2. Si el POLL recibe mensajes nuevos
            window.addEventListener('new-messages-received', () => {
                const distanceToBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight;

                if (distanceToBottom < 150) {
                    // Si está cerca del fondo, bajamos automáticamente
                    scrollToBottom();
                } else {
                    // Si está leyendo arriba, mostramos el aviso de Alpine
                    const alpineData = document.querySelector('[x-data]').__x.$data;
                    if(alpineData) alpineData.showNotice = true;
                }
            });

            // Al cargar la primera vez
            scrollToBottom();
        });
    </script>
</div>
