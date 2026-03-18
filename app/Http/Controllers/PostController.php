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
     */
    public function index()
    {
        $posts = Post::with('author')->latest()->paginate(15);
        return view('admin.blog.index', compact('posts'));
    }

    /**
     * Formulario de creación.
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
            'image_cropped'   => 'nullable|string', // El base64 del cropper
            'cta_id'          => 'nullable|exists:exam_types,id',
            'meta_title'      => 'nullable|max:60',
        ]);

        $imagePath = null;

        // Procesar imagen recortada (Base64)
        if ($request->filled('image_cropped')) {
            $imagePath = $this->uploadCroppedImage($request->input('image_cropped'));
        }

        Post::create([
            'author_id'      => auth()->id(),
            'title'          => $request->title,
            'slug'           => Str::slug($request->title),
            'summary'        => $request->summary,
            'content'        => $request->content,
            'featured_image' => $imagePath,
            'cta_id'         => $request->cta_id,
            'cta_type'       => $request->cta_id ? 'pack' : null,
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
     * Actualiza el post.
     */
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title'   => 'required|max:255',
            'content' => 'required',
            'cta_id'  => 'nullable|exists:exam_types,id',
            'image_cropped' => 'nullable|string',
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

        // Procesar nueva imagen si se recortó una
        if ($request->filled('image_cropped')) {
            // Borrar la anterior si existe
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            // Guardar la nueva
            $data['featured_image'] = $this->uploadCroppedImage($request->input('image_cropped'));
        }

        $post->update($data);

        return redirect()->route('admin.posts.index')
            ->with('status', 'Artículo actualizado correctamente.');
    }

    /**
     * Función auxiliar para procesar el Base64 del Cropper
     */
    private function uploadCroppedImage($base64Data)
    {
        try {
            // Extraer la extensión y el contenido
            // Formato esperado: data:image/webp;base64,UklGRv...
            $image = str_replace('data:image/webp;base64,', '', $base64Data);
            $image = str_replace('data:image/jpeg;base64,', '', $image);
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);

            $imageName = 'blog/' . Str::random(40) . '.webp';

            Storage::disk('public')->put($imageName, base64_decode($image));

            return $imageName;
        } catch (\Exception $e) {
            Log::error('Error al subir imagen recortada: ' . $e->getMessage());
            return null;
        }
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
     * MÉTODOS PÚBLICOS
     */
    public function publicIndex()
    {
        $posts = Post::where('is_published', true)->latest()->paginate(9);
        return view('blog.index', compact('posts'));
    }

    public function publicShow($slug)
    {
        $post = Post::where('slug', $slug)->where('is_published', true)->firstOrFail();
        return view('blog.show', compact('post'));
    }
}
