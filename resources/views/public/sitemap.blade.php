{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {{-- URL Principal --}}
    <url>
        <loc>{{ url('/') }}</loc>
        <priority>1.0</priority>
        <changefreq>daily</changefreq>
    </url>

    {{-- Blog Index --}}
    <url>
        <loc>{{ route('blog.index') }}</loc>
        <priority>0.8</priority>
    </url>

    {{-- Posts del Blog dinámicos --}}
    @foreach ($posts as $post)
        <url>
            <loc>{{ route('blog.show', $post->slug) }}</loc>
            <lastmod>{{ $post->updated_at->tz('UTC')->toAtomString() }}</lastmod>
            <priority>0.7</priority>
        </url>
    @endforeach

    {{-- Aquí agregarías el loop de tus Packs cuando los habilites --}}
</urlset>
