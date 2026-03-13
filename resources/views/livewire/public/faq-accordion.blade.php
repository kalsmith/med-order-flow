<section id="faq" class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Preguntas Frecuentes</h2>
            <p class="text-muted">Todo lo que necesitas saber sobre tu orden médica digital.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion accordion-flush shadow-sm rounded-4 overflow-hidden" id="accordionFaq">
                    @forelse($faqs as $faq)
                        <div class="accordion-item border-bottom" wire:key="faq-{{ $faq->id }}">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-{{ $faq->id }}">
                                    {{ $faq->question }}
                                </button>
                            </h2>
                            <div id="faq-collapse-{{ $faq->id }}" class="accordion-collapse collapse" data-bs-parent="#accordionFaq">
                                <div class="accordion-body text-muted lh-lg">
                                    {!! nl2br(e($faq->answer)) !!}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-chat-dots text-muted fs-1"></i>
                            <p class="text-muted mt-2">No hay preguntas disponibles en este momento.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
