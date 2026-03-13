<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ExamType;

class LandingController extends Controller
{
    public function index()
    {
        // 1. PACKS: Exámenes que son "Padres" (tienen registros en la tabla pivote como parent_id)
        $packs = ExamType::where('is_active', true)
            ->has('children')
            ->with('children')
            ->get();

        // 2. INDIVIDUALES: Exámenes que NO tienen hijos
        // Y que NO son hijos de otros packs (para que el Hemograma no salga repetido)
        $individuales = ExamType::where('is_active', true)
            ->doesntHave('children') // No es un pack
            ->whereDoesntHave('parents') // No es parte de un pack
            ->orderBy('name', 'asc')
            ->get();

        return view('welcome', compact('packs', 'individuales'));
    }
}
