@props(['title' => 'Panel'])
@php
    $user = auth()->user();
    use App\Support\Permissions as P;
    $nav = [
        ['Resumen', 'panel.dashboard', null, 'M3 12l9-9 9 9M5 10v10h14V10'],
        ['Prospectos', 'panel.leads.index', P::LEADS_MANAGE, 'M18 9a3 3 0 11-6 0 3 3 0 016 0zM6 20a6 6 0 0112 0'],
        ['Clientes', 'panel.clients.index', P::CLIENTS_MANAGE, 'M4 21V5a2 2 0 012-2h12a2 2 0 012 2v16M9 7h6M9 11h6M9 15h3'],
        ['Cotizaciones', 'panel.quotes.index', P::QUOTES_MANAGE, 'M9 12h6M9 16h6M7 3h7l5 5v13H7z'],
        ['Proyectos', 'panel.projects.index', null, 'M3 7h18M3 12h18M3 17h18'],
        ['Tareas', 'panel.tasks.index', null, 'M9 11l3 3 8-8M4 12v7h16'],
        ['Calendario', 'panel.calendar.index', null, 'M8 3v4M16 3v4M3 9h18M5 5h14v16H5z'],
        ['Finanzas', 'panel.finance.index', P::FINANCE_VIEW, 'M12 3v18M17 7H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6'],
        ['Suscripciones', 'panel.subscriptions.index', P::SUBSCRIPTIONS_MANAGE, 'M4 4v6h6M20 20v-6h-6M5 15a8 8 0 0014 3M19 9A8 8 0 005 6'],
        ['Portafolio', 'panel.portfolio.index', P::PORTFOLIO_MANAGE, 'M4 6h16v12H4zM4 14l4-4 4 4 3-3 5 5'],
        ['Usuarios', 'panel.users.index', P::USERS_MANAGE, 'M12 12a4 4 0 100-8 4 4 0 000 8zM4 21a8 8 0 0116 0'],
        ['Auditoría', 'panel.audit.index', P::AUDIT_VIEW, 'M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} · Rejas.dev</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <script>try{if(localStorage.getItem('rejas-theme')==='dark')document.documentElement.classList.add('dark')}catch(e){}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 dark:bg-slate-950 dark:text-slate-200">
<div class="flex min-h-screen">
    <aside data-sidebar class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-ink text-mist transition-transform lg:static lg:translate-x-0">
        <div class="flex h-16 items-center px-6">
            <a href="{{ route('panel.dashboard') }}" class="font-display text-base font-bold tracking-[0.18em] text-white">REJAS<span class="text-electric">.DEV</span></a>
        </div>
        <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-2" aria-label="Panel">
            @foreach ($nav as [$label, $route, $permission, $icon])
                @if (! $permission || $user->can($permission))
                    @php $active = $route === 'panel.dashboard' ? request()->routeIs($route) : request()->routeIs(Str::beforeLast($route, '.').'.*') @endphp
                    <a href="{{ route($route) }}" @class(['flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition', 'bg-white/10 text-white' => $active, 'hover:bg-white/5 hover:text-white' => ! $active]) @if($active) aria-current="page" @endif>
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="{{ $icon }}"/></svg>
                        {{ $label }}
                    </a>
                @endif
            @endforeach
        </nav>
        <div class="border-t border-white/10 p-4 text-sm">
            <p class="font-medium text-white">{{ $user->name }}</p>
            <p class="text-xs">{{ $user->roles->pluck('name')->join(', ') }}</p>
            <div class="mt-3 flex items-center gap-3">
                <button type="button" data-theme-toggle class="text-xs hover:text-white">Tema claro/oscuro</button>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-xs hover:text-white">Salir</button></form>
            </div>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-8 dark:border-slate-800 dark:bg-slate-900/90">
            <button type="button" data-sidebar-toggle class="rounded-md p-1.5 lg:hidden" aria-label="Menú">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <h1 class="truncate font-display text-lg font-semibold text-slate-900 dark:text-white">{{ $title }}</h1>
            <div class="ml-auto flex items-center gap-2">{{ $actions ?? '' }}</div>
        </header>

        <main class="mx-auto w-full max-w-7xl flex-1 space-y-6 px-4 py-6 sm:px-8">
            @if (session('status'))
                <div role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200">
                    <ul class="list-inside list-disc space-y-0.5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
