<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FaqController extends Controller
{
    /**
     * Listado de contenidos informativos.
     */
    public function index()
    {
        // Agrupamos por categoría para que en la administración sea más ordenado
        $faqs = Faq::orderBy('category')->orderBy('order', 'asc')->get();
        return view('admin.faqs.index', compact('faqs'));
    }

    /**
     * Formulario para crear nuevo contenido.
     */
    public function create()
    {
        return view('admin.faqs.create');
    }

    /**
     * Guarda el registro con slug automático.
     */
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer'   => 'required|string',
            'category' => 'required|string', // faq, legal, devoluciones, etc.
            'order'    => 'nullable|integer',
            'is_active'=> 'nullable|boolean',
        ]);

        $data = $request->all();

        // Generamos slug automático para URLs amigables
        $data['slug'] = Str::slug($request->question);

        // Manejo de orden automático si viene vacío
        if (!$request->filled('order')) {
            $data['order'] = Faq::where('category', $request->category)->max('order') + 1;
        }

        Faq::create($data);

        return redirect()->route('admin.faqs.index')
            ->with('success', 'Contenido informativo creado con éxito.');
    }

    public function show(Faq $faq)
    {
        return view('admin.faqs.show', compact('faq'));
    }

    public function edit(Faq $faq)
    {
        return view('admin.faqs.edit', compact('faq'));
    }

    /**
     * Actualiza el contenido y el slug.
     */
    public function update(Request $request, Faq $faq)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer'   => 'required|string',
            'category' => 'required|string',
            'order'    => 'required|integer',
            'is_active'=> 'required|boolean',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->question);

        $faq->update($data);

        return redirect()->route('admin.faqs.index')
            ->with('success', 'Contenido actualizado correctamente.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')
            ->with('success', 'Registro eliminado correctamente.');
    }
}
