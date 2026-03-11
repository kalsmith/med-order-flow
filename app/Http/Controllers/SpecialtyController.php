<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SpecialtyController extends Controller
{
    /**
     * Lista todas las especialidades ordenadas alfabéticamente.
     */
    public function index()
    {
        $specialties = Specialty::orderBy('name')->get();
        return view('admin.specialties.index', compact('specialties'));
    }

    public function create()
    {
        return view('admin.specialties.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|unique:specialties,name|max:255',
            'description' => 'nullable|string|max:500'
        ], [
            'name.unique' => 'Esta especialidad ya se encuentra registrada.'
        ]);

        try {
            Specialty::create([
                'name'        => $request->name,
                'slug'        => Str::slug($request->name),
                'description' => $request->description
            ]);

            return redirect()->route('admin.specialties.index')
                ->with('status', 'Especialidad creada con éxito.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'No se pudo crear la especialidad.']);
        }
    }

    public function edit(Specialty $specialty)
    {
        // Pasamos la variable explícitamente para evitar problemas de nombres en las rutas
        return view('admin.specialties.edit', compact('specialty'));
    }

    public function update(Request $request, Specialty $specialty)
    {
        $request->validate([
            'name'        => [
                'required',
                'max:255',
                Rule::unique('specialties')->ignore($specialty->id)
            ],
            'description' => 'nullable|string|max:500'
        ]);

        try {
            $specialty->update([
                'name'        => $request->name,
                'slug'        => Str::slug($request->name),
                'description' => $request->description
            ]);

            return redirect()->route('admin.specialties.index')
                ->with('status', 'Especialidad actualizada correctamente.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    /**
     * Opcional: Eliminar especialidad
     */
    public function destroy(Specialty $specialty)
    {
        // Verificar si tiene doctores asociados antes de borrar (opcional)
        if ($specialty->doctors()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar una especialidad con doctores asignados.']);
        }

        $specialty->delete();
        return redirect()->route('admin.specialties.index')
            ->with('status', 'Especialidad eliminada correctamente.');
    }
}
