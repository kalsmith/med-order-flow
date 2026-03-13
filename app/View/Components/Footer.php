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
        // Buscamos los datos aquí para que estén disponibles en todas las vistas
        $faqs = Faq::active()
            ->whereIn('category', ['faq', 'legal', 'como-funciona'])
            ->orderBy('order', 'asc')
            ->get()
            ->groupBy('category');

        return view('components.footer', compact('faqs'));
    }
}
