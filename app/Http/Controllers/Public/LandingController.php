<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use App\Models\Post;

class LandingController extends Controller
{
public function index()
{
    // 1. PACKS: Máximo 16, orden aleatorio para que la vitrina cambie siempre.
    $packs = ExamType::where('is_active', true)
        ->has('children')
        ->with('children:id,name')
        // ->inRandomOrder() // <--- Hace que la selección sea aleatoria
        ->limit(16)       // <--- Máximo 16 registros
        ->get();

    // 2. INDIVIDUALES: Máximo 16, también aleatorios.
    $individuales = ExamType::where('is_active', true)
        ->doesntHave('children')
        ->inRandomOrder()
        ->limit(16)
        ->get();

    return view('welcome', compact('packs', 'individuales'));
}

    public function sitemap()
    {
        $posts = Post::where('is_published', true)->latest()->get();
        // $packs = Pack::all();

        return response()->view('public.sitemap', [
            'posts' => $posts,
            // 'packs' => $packs
        ])->header('Content-Type', 'text/xml');
    }

}
