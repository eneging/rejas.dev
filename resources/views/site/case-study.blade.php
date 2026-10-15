<x-layouts.site :title="$project->title" :description="$project->summary">
    <article class="site-container max-w-3xl pt-36 pb-24">
        <a href="{{ route('home') }}#proyectos" class="text-sm text-mist hover:text-white">← Proyectos</a>
        <p class="eyebrow mt-10">{{ $project->sector }}</p>
        <h1 class="mt-4 text-4xl font-bold sm:text-6xl">{{ $project->title }}</h1>
        <p class="mt-6 text-xl text-mist">{{ $project->summary }}</p>
        @foreach (['challenge' => 'El reto', 'solution' => 'La solución', 'results' => 'Resultados'] as $field => $heading)
            @if ($project->{$field})
                <section class="mt-14" data-reveal>
                    <h2 class="text-2xl font-semibold">{{ $heading }}</h2>
                    <div class="mt-4 space-y-4 leading-relaxed text-mist">{!! nl2br(e($project->{$field})) !!}</div>
                </section>
            @endif
        @endforeach
        @if ($project->technologies)
            <p class="mt-14 flex flex-wrap gap-2">@foreach ($project->technologies as $tech)<span class="rounded-full border border-white/10 px-3 py-1 text-xs text-mist">{{ $tech }}</span>@endforeach</p>
        @endif
        <a href="{{ route('inquiry.create') }}" class="btn-site mt-14 bg-electric text-white hover:bg-electric-700">Quiero algo similar</a>
    </article>
</x-layouts.site>
