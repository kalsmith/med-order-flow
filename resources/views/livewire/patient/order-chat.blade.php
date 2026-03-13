{{-- El wire:poll.5s hace que el componente se refresque cada 5 segundos --}}
<div class="patient-chat-container" wire:poll.5s="refreshMessages">

    <div class="chat-messages mb-3 p-3"
         id="chat-box-{{ $order->id }}"
         style="height: 350px; overflow-y: auto; background: #f8fafc; border-radius: 12px; scroll-behavior: smooth;">

        @foreach($order->interactions as $interaction)
            @php $isFromDoctor = ($interaction->sender_type === 'doctor'); @endphp

            <div class="d-flex {{ $isFromDoctor ? 'justify-content-start' : 'justify-content-end' }} mb-3">
                <div class="message-bubble {{ $isFromDoctor ? 'doctor-msg' : 'patient-msg' }} shadow-sm">
                    @if($isFromDoctor)
                        <span class="d-block fw-bold mb-1 text-primary" style="font-size: 0.65rem;">MÉDICO</span>
                    @endif

                    <p class="mb-0">{{ $interaction->content }}</p>

                    <small class="msg-time">{{ $interaction->created_at->format('H:i') }}</small>
                </div>
            </div>
        @endforeach
    </div>

    <form wire:submit.prevent="sendMessage" class="chat-input-wrapper">
        <div class="input-group shadow-sm border rounded-pill overflow-hidden bg-white">
            <input type="text"
                   wire:model.defer="newMessage"
                   class="form-control border-0 px-4 py-2"
                   placeholder="Escribe tu respuesta al médico..."
                   style="box-shadow: none;">
            <button class="btn btn-primary px-4" type="submit" wire:loading.attr="disabled">
                <i class="bi bi-send-fill" wire:loading.remove></i>
                <div class="spinner-border spinner-border-sm" role="status" wire:loading></div>
            </button>
        </div>
    </form>

    {{-- Script de scroll automático específico para este componente --}}
    <script>
        window.addEventListener('scroll-bottom', () => {
            const chatBox = document.getElementById('chat-box-{{ $order->id }}');
            if (chatBox) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        });

        // También bajar el scroll al iniciar si el colapsable se abre
        document.addEventListener('DOMContentLoaded', () => {
            const chatBox = document.getElementById('chat-box-{{ $order->id }}');
            if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
        });
    </script>
</div>
