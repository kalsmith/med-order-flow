<div class="chat-container d-flex flex-column" style="height: 400px;">
    <div class="chat-messages flex-grow-1 overflow-y-auto p-3 bg-light"
         id="chat-window-{{ $order->id }}"
         wire:poll.5s="refreshMessages">

        @foreach($messages as $msg)
            <div class="d-flex mb-3 {{ $msg->sender_type === 'patient' ? 'justify-content-end' : 'justify-content-start' }}">
                <div class="message-bubble p-3 rounded-4 shadow-sm {{ $msg->sender_type === 'patient' ? 'bg-primary text-white' : 'bg-white border' }}" style="max-width: 80%;">
                    <p class="mb-1 small">{{ $msg->content }}</p>
                    <span class="smaller opacity-75 d-block text-end" style="font-size: 0.7rem;">
                        {{ $msg->created_at->format('H:i') }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="p-3 border-top bg-white">
        @if($canPatientReply)
            <form wire:submit.prevent="sendMessage" class="d-flex gap-2">
                <input type="text"
                       wire:model.defer="newMessage"
                       class="form-control rounded-pill border-0 bg-light px-3"
                       placeholder="Escribe tu respuesta...">
                <button type="submit" class="btn btn-primary btn-circle rounded-circle">
                    <i class="bi bi-send-fill"></i>
                </button>
            </form>
        @else
            <div class="alert alert-info small rounded-4 mb-0 py-2">
                <i class="bi bi-info-circle me-1"></i> Esperando instrucciones del médico para habilitar respuesta.
            </div>
        @endif
    </div>

    <script>
        window.addEventListener('scroll-bottom', event => {
            const container = document.getElementById('chat-window-{{ $order->id }}');
            if(container) {
                container.scrollTop = container.scrollHeight;
            }
        });

        // Hacer scroll al cargar el componente
        document.addEventListener('livewire:load', () => {
            const container = document.getElementById('chat-window-{{ $order->id }}');
            if(container) container.scrollTop = container.scrollHeight;
        });
    </script>
</div>
