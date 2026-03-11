<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LandingController extends Controller
{
    public function index()
    {
        $data = Cache::remember('landing_exams_offer', 3600, function () {
            return [
                // 1. PACKS: Exámenes que TIENEN hijos en la tabla pivote
                'packs' => ExamType::where('is_active', true)
                    ->has('children')
                    ->with('children:id,name') // Quitamos 'parent_id' porque no existe en la tabla
                    ->orderBy('base_price', 'asc')
                    ->get(),

                // 2. INDIVIDUALES: Exámenes que NO tienen hijos
                // Y que NO son hijos de nadie (para que no aparezcan duplicados)
                'individuales' => ExamType::where('is_active', true)
                    ->doesntHave('children')
                    ->doesntHave('parents') // Esta es la forma correcta con tu relación Many-to-Many
                    ->take(8)
                    ->get()
            ];
        });

        return view('welcome', [
            'packs'        => $data['packs'],
            'individuales' => $data['individuales']
        ]);
    }
}
