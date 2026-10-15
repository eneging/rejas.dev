@props(['title' => null, 'description' => 'Rejas.dev desarrolla software a medida y soluciones por rubro para talleres, colegios, inventarios y finanzas.'])
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' · ' : '' }}Rejas.dev — Desarrollo de software a medida</title>
    <meta name="description" content="{{ $description }}">
    <meta property="og:title" content="{{ $title ?? 'Rejas.dev' }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:type" content="website">
    <meta name="theme-color" content="#080B12">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/site.js'])
</head>
<body class="min-h-screen bg-ink font-sans text-white antialiased selection:bg-electric/60">
    <a href="#contenido" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded focus:bg-white focus:px-3 focus:py-2 focus:text-ink">Saltar al contenido</a>

    <header data-site-header class="fixed inset-x-0 top-0 z-40 border-b border-transparent transition-colors">
        <nav class="site-container flex h-16 items-center justify-between" aria-label="Principal">
            <a href="{{ route('home') }}" class="font-display text-lg font-bold tracking-[0.18em]">REJAS<span class="text-electric">.DEV</span></a>
            <button type="button" data-menu-toggle class="rounded-md p-2 text-mist md:hidden" aria-label="Abrir menú">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <div data-menu class="absolute inset-x-0 top-16 hidden border-b border-white/10 bg-ink/95 px-5 py-4 md:static md:flex md:items-center md:gap-8 md:border-0 md:bg-transparent md:p-0">
                <a href="{{ route('home') }}#servicios" class="block py-2 text-sm text-mist hover:text-white">Servicios</a>
                <a href="{{ route('home') }}#soluciones" class="block py-2 text-sm text-mist hover:text-white">Soluciones</a>
                <a href="{{ route('home') }}#proyectos" class="block py-2 text-sm text-mist hover:text-white">Proyectos</a>
                <a href="{{ route('workshop') }}" class="block py-2 text-sm text-mist hover:text-white">Talleres</a>
                <a href="{{ route('inquiry.create') }}" class="btn-site mt-2 bg-electric text-white hover:bg-electric-700 md:mt-0">Solicitar cotización</a>
            </div>
        </nav>
    </header>

    <main id="contenido">{{ $slot }}</main>

    <footer class="border-t border-white/10 py-12">
        <div class="site-container flex flex-col gap-8 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="font-display text-lg font-bold tracking-[0.18em]">REJAS<span class="text-electric">.DEV</span></p>
                <p class="mt-2 max-w-sm text-sm text-mist">Desarrollamos tecnología que impulsa tu negocio. {{ config('rejas.domain') }}</p>
            </div>
            <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-mist">
                <a href="mailto:{{ config('rejas.contact_email') }}" class="hover:text-white">{{ config('rejas.contact_email') }}</a>
                <a href="{{ route('privacy') }}" class="hover:text-white">Política de privacidad</a>
                <a href="{{ route('terms') }}" class="hover:text-white">Términos</a>
                <a href="{{ route('login') }}" class="hover:text-white">Acceso equipo</a>
            </div>
        </div>
        <p class="site-container mt-8 text-xs text-mist/60">© {{ date('Y') }} Rejas.dev. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
