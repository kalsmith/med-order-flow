<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Faq; // <-- No olvides importar el modelo

class Footer extends Component
{
    public function __construct()
    {
        //
    }

public function render(): View|Closure|string
{
    // Traemos todo lo activo sin importar la categoría
    $faqs = Faq::active()
        ->orderBy('order', 'asc')
        ->get();

    return view('components.footer', compact('faqs'));
}
}
