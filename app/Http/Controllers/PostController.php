<?php

namespace App\Http\Controllers;

use App\Models\ExamType;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Muestra el listado de posts para el personal del staff.
     * Ruta: admin.posts.index
     */
    public function index()
    {
        $posts = Post::with('author')->latest()->paginate(15);
        return view('admin.blog.index', compact('posts'));
    }

    /**
     * Formulario de creación.
     * Ruta: admin.posts.create
     */
    public function create()
    {
        $examTypes = ExamType::where('is_active', true)->orderBy('name')->get();
        return view('admin.blog.create', compact('examTypes'));
    }

    /**
     * Guarda el nuevo post.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'required|max:255',
            'summary'         => 'required|max:500',
            'content'         => 'required',
            'featured_image'  => 'nullable|image|max:2048',
            'cta_id'          => 'nullable|exists:exam_types,id',
            'meta_title'      => 'nullable|max:60',
        ]);

        $imagePath = null;
        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('blog', 'public');
        }

        Post::create([
            'author_id'      => auth()->id(),
            'title'          => $request->title,
            'slug'           => Str::slug($request->title),
            'summary'        => $request->summary,
            'content'        => $request->content,
            'featured_image' => $imagePath,
            'cta_id'         => $request->cta_id,
            'cta_type'       => $request->cta_id ? 'pack' : null, // Lógica simple por ahora
            'meta_title'     => $request->meta_title ?? $request->title,
            'meta_keywords'  => $request->meta_keywords,
            'is_published'   => $request->has('is_published'),
            'published_at'   => $request->has('is_published') ? now() : null,
        ]);

        return redirect()->route('admin.posts.index')
            ->with('status', 'Artículo de blog creado con éxito.');
    }

    /**
     * Formulario de edición.
     * Ruta: admin.posts.edit
     */
    public function edit(Post $post)
    {
        $examTypes = ExamType::where('is_active', true)->orderBy('name')->get();
        return view('admin.blog.edit', compact('post', 'examTypes'));
    }

    /**
     * Actualiza el post.
     */

public function update(Request $request, Post $post)
{
    // LOG 1: Ver qué llega exactamente
    Log::info('Iniciando Update de Post ID: ' . $post->id, [
        'all_data' => $request->all(),
        'has_file' => $request->hasFile('featured_image'),
        'cta_id' => $request->input('cta_id')
    ]);

    $request->validate([
        'title'   => 'required|max:255',
        'content' => 'required',
        'cta_id'  => 'nullable|exists:exam_types,id',
        'featured_image' => 'nullable|image|max:2048', // Agregamos validación aquí también
    ]);

    $data = $request->only([
        'title', 'summary', 'content', 'cta_id',
        'meta_title', 'meta_keywords'
    ]);

    $data['slug'] = Str::slug($request->title);
    $data['is_published'] = $request->has('is_published');
    $data['cta_type'] = $request->cta_id ? 'pack' : null;

    if ($data['is_published'] && !$post->published_at) {
        $data['published_at'] = now();
    }

    if ($request->hasFile('featured_image')) {
        Log::info('Imagen detectada, procesando subida...');
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }
        $data['featured_image'] = $request->file('featured_image')->store('blog', 'public');
    }

    // LOG 2: Ver qué datos se le envían al método update()
    Log::info('Datos finales para update:', $data);

    $updated = $post->update($data);

    Log::info('Resultado del update:', ['success' => $updated]);

    return redirect()->route('admin.posts.index')
        ->with('status', 'Artículo actualizado correctamente.');
}


    /**
     * Elimina el post.
     */
    public function destroy(Post $post)
    {
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }

        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('status', 'Artículo eliminado.');
    }

    /**
     * MÉTODOS PARA EL FRONTEND (Público)
     * Estos se llaman desde rutas fuera del prefijo 'gestion'
     */
    public function publicIndex()
    {
        $posts = Post::published()->latest()->paginate(9);
        return view('blog.index', compact('posts'));
    }

    public function publicShow($slug)
    {
        $post = Post::where('slug', $slug)->published()->firstOrFail();
        return view('blog.show', compact('post'));
    }
}
