<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ExamType;

class LandingController extends Controller
{
    public function index()
    {
        // 1. PACKS: Exámenes que TIENEN hijos (la estructura del bundle)
        $packs = ExamType::where('is_active', true)
            ->has('children')
            ->with('children:id,name')
            ->orderBy('base_price', 'asc')
            ->get();

        // 2. INDIVIDUALES: Exámenes que NO TIENEN hijos.
        // Aquí NO filtramos por "parents", así que si el Hemograma (ID 7)
        // está en un pack, igual aparecerá aquí porque él mismo no es un contenedor.
        $individuales = ExamType::where('is_active', true)
            ->doesntHave('children')
            ->orderBy('name', 'asc')
            ->get();

        return view('welcome', compact('packs', 'individuales'));
    }
}
