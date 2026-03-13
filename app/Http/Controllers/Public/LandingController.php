<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ExamType;

class LandingController extends Controller
{
    public function index()
    {
        // 1. PACKS: Exámenes activos que tienen hijos (baterías de exámenes)
        $packs = ExamType::where('is_active', true)
            ->has('children')
            ->with('children:id,name')
            ->orderBy('base_price', 'asc')
            ->get();

        // 2. INDIVIDUALES: Exámenes activos que no son packs ni pertenecen a uno
        $individuales = ExamType::where('is_active', true)
            ->doesntHave('children')
            ->doesntHave('parents')
            ->orderBy('name', 'asc')
            ->get(); // Quitamos el take() para que veas absolutamente todo lo cargado

        return view('welcome', [
            'packs'        => $packs,
            'individuales' => $individuales
        ]);
    }
}
