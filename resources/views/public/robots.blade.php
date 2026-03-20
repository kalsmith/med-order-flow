# ---------------------------------------------------------
# Robots.txt para {{ config('app.name') }}
# Generado dinámicamente para: {{ url('/') }}
# ---------------------------------------------------------

User-agent: *

# Bloquear directorios
Disallow: /gestion/
Disallow: /vendor/
Disallow: /storage/

# Bloquear procesos de usuario
Disallow: /solicitar/
Disallow: /checkout/
Disallow: /mis-ordenes/
Disallow: /perfil/

# Permitir contenido SEO
Allow: /
Allow: /blog/
Allow: /assets/

# La URL del Sitemap se ajusta sola
Sitemap: {{ url('sitemap.xml') }}
