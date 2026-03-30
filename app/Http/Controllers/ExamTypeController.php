<?php

namespace App\Http\Controllers;

use App\Models\ExamType;
use App\Models\Post;
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
        $posts = Post::orderBy('title')->get(); // <--- IMPORTANTE: Cargar los posts

        // Filtramos: Solo exámenes que NO tengan hijos (individuales)
        // Ojo: Si quieres permitir "Packs de Packs", quita el doesntHave
        $allExams = ExamType::doesntHave('children')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.exam_types.create', compact('specialties', 'allExams','posts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string|max:500', // Validación para el Slogan SEO
            'specialty_id' => 'required|exists:specialties,id',
            'code_fonasa'  => 'nullable|string|max:20',
            'base_price'   => 'required|integer|min:0',
            'is_active'    => 'nullable|boolean',
            'bundle_ids'   => 'nullable|array',
            'bundle_ids.*' => 'exists:exam_types,id'
        ]);

        try {
            DB::beginTransaction();

            // Agregamos 'description' al create
            $examType = ExamType::create($request->only([
                'name', 'description', 'specialty_id', 'code_fonasa', 'base_price', 'is_active'
            ]));

            if ($request->has('bundle_ids')) {
                $examType->children()->sync($request->bundle_ids);
            }

            DB::commit();
            return redirect()->route('admin.exam-types.index')->with('status', 'Examen creado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Error al crear: ' . $e->getMessage()]);
        }
    }

    public function edit(ExamType $examType)
    {
        $specialties = Specialty::all();

        // CARGAR LOS POSTS PARA EL SELECTOR DEL BLOG
        $posts = Post::orderBy('title')->get();

        // Filtramos: Exámenes individuales y excluimos al actual
        $allExams = ExamType::doesntHave('children')
            ->where('id', '!=', $examType->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Asegúrate de incluir 'posts' en el compact
        return view('admin.exam_types.edit', compact('examType', 'specialties', 'allExams', 'posts'));
    }

    public function update(Request $request, ExamType $examType)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string|max:500',
            'post_id'      => 'nullable|exists:blog_posts,id', // <--- Nueva validación
            'specialty_id' => 'required|exists:specialties,id',
            'base_price'   => 'required|integer|min:0',
            'is_active'    => 'nullable|boolean',
            'bundle_ids'   => 'nullable|array',
            'bundle_ids.*' => 'exists:exam_types,id'
        ]);

        try {
            DB::beginTransaction();

            // Agregamos 'post_id' al update
            $examType->update($request->only([
                'name', 'description', 'post_id', 'specialty_id', 'code_fonasa', 'base_price', 'is_active'
            ]));

            $examType->children()->sync($request->bundle_ids ?? []);

            DB::commit();

            return redirect()->route('admin.exam-types.index')
                            ->with('status', '¡Éxito! El examen "' . $examType->name . '" ha sido actualizado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }


    public function destroy(ExamType $examType)
    {
        try {
            // No necesitamos DB::beginTransaction para un simple SoftDelete

            // 1. Opcional: Validar si quieres impedir el borrado lógico si está en un pack
            if ($examType->parents()->exists()) {
                return back()->withErrors(['error' => 'No puedes eliminar este examen porque es parte de un Pack activo.']);
            }

            // 2. Borrado lógico (Llenará deleted_at automáticamente)
            $examType->delete();

            return redirect()->route('admin.exam-types.index')
                ->with('status', 'El examen "' . $examType->name . '" ha sido movido a la papelera.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }



}
