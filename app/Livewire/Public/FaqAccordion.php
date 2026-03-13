<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Faq;

class FaqAccordion extends Component
{
    public function render()
    {
        return view('livewire.public.faq-accordion', [
            'faqs' => Faq::active()
                ->where('category', 'faq')
                ->orderBy('order', 'asc')
                ->get()
        ]);
    }
}
