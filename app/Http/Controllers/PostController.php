<?php

namespace App\Http\Controllers;

use App\Models\ExamType;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Muestra el listado de posts para el personal del staff.
     */
    public function index()
    {
        $posts = Post::with('author')->latest()->paginate(15);
        return view('admin.blog.index', compact('posts'));
    }

    /**
     * Formulario de creación con listado de ExamTypes para asociar.
     */
    public function create()
    {
        // Traemos todos los ExamTypes.
        // En la vista puedes separarlos por "Packs" (los que tienen children) y "Exámenes".
        $examTypes = ExamType::where('is_active', true)->orderBy('name')->get();
        return view('admin.blog.create', compact('examTypes'));
    }

    /**
     * Guarda el nuevo post con su SEO e Imagen.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'required|max:255',
            'summary'         => 'required|max:500',
            'content'         => 'required',
            'featured_image'  => 'nullable|image|max:2048',
            'cta_id'          => 'nullable|exists:exam_types,id',
            'cta_type'        => 'nullable|in:pack,exam,custom_flow',
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
            'cta_id'         => $request->cta_id,   // Mapeado a tu migración
            'cta_type'       => $request->cta_type ?? ($request->cta_id ? 'pack' : null),
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
     */
    public function edit(Post $post)
    {
        $examTypes = ExamType::where('is_active', true)->orderBy('name')->get();
        return view('admin.blog.edit', compact('post', 'examTypes'));
    }

    /**
     * Actualiza el post y gestiona la imagen antigua.
     */
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title'   => 'required|max:255',
            'content' => 'required',
            'cta_id'  => 'nullable|exists:exam_types,id',
        ]);

        $data = $request->only([
            'title', 'summary', 'content', 'cta_id',
            'cta_type', 'meta_title', 'meta_keywords'
        ]);

        $data['slug'] = Str::slug($request->title);
        $data['is_published'] = $request->has('is_published');

        // Si se publica ahora y no tenía fecha, se la ponemos
        if ($data['is_published'] && !$post->published_at) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')->store('blog', 'public');
        }

        $post->update($data);

        return redirect()->route('admin.posts.index')
            ->with('status', 'Artículo actualizado correctamente.');
    }

    /**
     * Elimina el post y su imagen.
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
}
