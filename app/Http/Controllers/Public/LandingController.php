<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ExamType;

class LandingController extends Controller
{
public function index()
{
    $packs = ExamType::where('is_active', true)
        ->has('children')
        ->with('children:id,name')
        ->get();

    $individuales = ExamType::where('is_active', true)
        ->doesntHave('children')
        ->whereDoesntHave('parents')
        ->get();

    dd([
        'Total Packs Encontrados' => $packs->count(),
        'Nombres de Packs' => $packs->pluck('name')->toArray(),
        'Total Individuales Encontrados' => $individuales->count(),
        'Nombres de Individuales' => $individuales->pluck('name')->toArray(),
        'SQL Individuales' => ExamType::where('is_active', true)
            ->doesntHave('children')
            ->whereDoesntHave('parents')
            ->toSql()
    ]);
}
}
