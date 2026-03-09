<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SpecialtyController extends Controller
{
    public function index()
    {
        $specialties = Specialty::all();
        return view('admin.specialties.index', compact('specialties'));
    }

    public function create()
    {
        return view('admin.specialties.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:specialties|max:255',
            'description' => 'nullable'
        ]);

        Specialty::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description
        ]);

        return redirect()->route('specialties.index')->with('status', 'Especialidad creada con éxito.');
    }

    public function edit(Specialty $specialty)
    {
        return view('admin.specialties.edit', compact('specialty'));
    }

    public function update(Request $request, Specialty $specialty)
    {
        $request->validate([
            'name' => 'required|max:255|unique:specialties,name,' . $specialty->id,
            'description' => 'nullable'
        ]);

        $specialty->update([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'description' => $request->description
        ]);

        return redirect()->route('specialties.index')->with('status', 'Especialidad actualizada correctamente.');
    }


}
