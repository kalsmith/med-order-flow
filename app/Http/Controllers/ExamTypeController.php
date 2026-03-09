<?php

namespace App\Http\Controllers;

use App\Models\ExamType;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamTypeController extends Controller
{
    public function index()
    {
        // Cargamos la especialidad y contamos los hijos para saber si es un pack
        $exams = ExamType::with('specialty')
            ->withCount('children')
            ->latest()
            ->paginate(15);

        return view('admin.exam_types.index', compact('exams'));
    }

    public function create()
    {
        $specialties = Specialty::all();
        return view('admin.exam_types.create', compact('specialties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'specialty_id' => 'required|exists:specialties,id',
            'code_fonasa' => 'nullable|string|max:20',
            'base_price' => 'required|integer|min:0',
        ]);

        ExamType::create($request->all());

        return redirect()->route('exam-types.index')->with('status', 'Examen creado exitosamente.');
    }

    public function edit(ExamType $examType)
    {
        $specialties = Specialty::all();

        // Obtenemos todos los exámenes para poder armar la batería
        // Excluimos el examen actual para evitar circularidad
        $allExams = ExamType::where('id', '!=', $examType->id)
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

            // Actualizamos los datos básicos
            $examType->update($request->only([
                'name', 'specialty_id', 'code_fonasa', 'base_price', 'is_active'
            ]));

            // Sincronizamos la batería de exámenes (la pila)
            // Si el array viene vacío, sync([]) eliminará todas las relaciones
            $examType->children()->sync($request->bundle_ids ?? []);

            DB::commit();
            return redirect()->route('exam-types.index')->with('status', 'Examen y batería actualizados.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }
}
