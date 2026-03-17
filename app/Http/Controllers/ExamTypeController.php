<?php

namespace App\Http\Controllers;

use App\Models\ExamType;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamTypeController extends Controller
{

public function index(Request $request)
{
    $query = ExamType::with(['specialty', 'parents'])->withCount('children');

    // Filtro por nombre o código
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('code_fonasa', 'LIKE', "%{$search}%");
        });
    }

    // Filtro por Especialidad
    if ($request->filled('specialty_id')) {
        $query->where('specialty_id', $request->specialty_id);
    }

    // Filtro por Tipo (Pack o Individual)
    if ($request->filled('type')) {
        if ($request->type === 'pack') {
            $query->has('children');
        } elseif ($request->type === 'individual') {
            $query->doesntHave('children');
        }
    }

    $exams = $query->latest()->paginate(15)->withQueryString();
    $specialties = Specialty::orderBy('name')->get();

    return view('admin.exam_types.index', compact('exams', 'specialties'));
}
public function create()
{
    $specialties = Specialty::all();

    // Filtramos: Solo exámenes que NO tengan hijos (individuales)
    $allExams = ExamType::doesntHave('children')
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    return view('admin.exam_types.create', compact('specialties', 'allExams'));
}

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'specialty_id' => 'required|exists:specialties,id',
            'code_fonasa' => 'nullable|string|max:20',
            'base_price' => 'required|integer|min:0',
            'bundle_ids' => 'nullable|array',
            'bundle_ids.*' => 'exists:exam_types,id'
        ]);

        try {
            DB::beginTransaction();

            // 1. Crear el examen principal
            $examType = ExamType::create($request->only([
                'name', 'specialty_id', 'code_fonasa', 'base_price', 'is_active'
            ]));

            // 2. Si seleccionó exámenes para el pack, los vinculamos
            if ($request->has('bundle_ids')) {
                $examType->children()->sync($request->bundle_ids);
            }

            DB::commit();
            // IMPORTANTE: Verifica el nombre de la ruta, pusimos 'admin.exam-types.index' en web.php
            return redirect()->route('admin.exam-types.index')->with('status', 'Examen creado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear: ' . $e->getMessage()]);
        }
    }

public function edit(ExamType $examType)
{
    $specialties = Specialty::all();

    // Filtramos: Solo exámenes individuales y excluimos al actual
    $allExams = ExamType::doesntHave('children')
        ->where('id', '!=', $examType->id)
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    return view('admin.exam_types.edit', compact('examType', 'specialties', 'allExams'));
}

    public function update(Request $request, ExamType $examType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'specialty_id' => 'required|exists:specialties,id',
            'base_price' => 'required|integer|min:0',
            'bundle_ids' => 'nullable|array',
            'bundle_ids.*' => 'exists:exam_types,id'
        ]);

    try {
            DB::beginTransaction();

            $examType->update($request->only([
                'name', 'specialty_id', 'code_fonasa', 'base_price', 'is_active'
            ]));

            $examType->children()->sync($request->bundle_ids ?? []);

            DB::commit();

            // CAMBIO AQUÍ: Agregamos 'admin.' a la ruta y enviamos el mensaje
            return redirect()->route('admin.exam-types.index')
                            ->with('status', '¡Éxito! El examen "' . $examType->name . '" ha sido actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }
}
