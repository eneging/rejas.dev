@php use App\Support\Money; use App\Support\Permissions as P; @endphp
<x-layouts.panel title="Resumen">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach (['verde' => ['En plazo', 'text-emerald-600'], 'amarillo' => ['Por vencer (≤ 5 días)', 'text-amber-600'], 'rojo' => ['Vencidos', 'text-red-600']] as $key => [$label, $color])
            <a href="{{ route('panel.projects.index', ['semaforo' => $key]) }}" class="card hover:border-electric/50">
                <p class="muted">{{ $label }}</p>
                <p class="mt-1 font-display text-3xl font-semibold {{ $color }}">{{ $lights[$key] ?? 0 }}</p>
            </a>
        @endforeach
        @can(P::LEADS_MANAGE)
            <a href="{{ route('panel.leads.index', ['estado' => 'nuevo']) }}" class="card hover:border-electric/50">
                <p class="muted">Prospectos nuevos · cotizaciones abiertas</p>
                <p class="mt-1 font-display text-3xl font-semibold">{{ $newLeads }} <span class="text-slate-400">·</span> {{ $openQuotes }}</p>
            </a>
        @endcan
    </div>

    @can(P::FINANCE_VIEW)
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="card"><p class="muted">Por cobrar en proyectos activos</p><p class="mt-1 font-display text-2xl font-semibold">{{ Money::format($receivable) }}</p></div>
            <div class="card"><p class="muted">Suscripciones por cobrar</p><p class="mt-1 font-display text-2xl font-semibold">{{ Money::format($pendingCharges) }}</p></div>
        </div>
    @endcan

    <div class="grid gap-6 lg:grid-cols-[1.6fr_1fr]">
        <section class="card overflow-x-auto p-0">
            <div class="flex items-center justify-between px-5 pt-5"><h2 class="font-semibold">Proyectos activos</h2><a href="{{ route('panel.projects.index') }}" class="link text-sm">Ver tabla</a></div>
            @if ($projects->isEmpty())
                <div class="p-5"><x-empty>No hay proyectos activos.</x-empty></div>
            @else
                <table class="table mt-3">
                    <thead><tr><th>Proyecto</th><th>Entrega interna</th><th>Avance</th><th>Semáforo</th></tr></thead>
                    <tbody>
                    @foreach ($projects->take(10) as $project)
                        <tr>
                            <td><a href="{{ route('panel.projects.show', $project) }}" class="font-medium hover:text-electric">{{ $project->name }}</a><p class="text-xs text-slate-500">{{ $project->client->displayName() }}</p></td>
                            <td class="whitespace-nowrap">{{ $project->internal_due_date?->format('d/m/Y') ?? '—' }}</td>
                            <td><x-progress-bar :value="$project->progress()" /></td>
                            <td><x-traffic-light :project="$project" />@if($project->isBehindSchedule())<span class="badge mt-1 bg-amber-50 text-amber-700">Ritmo bajo</span>@endif</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <section class="card">
            <div class="flex items-center justify-between"><h2 class="font-semibold">Mis tareas</h2><a href="{{ route('panel.tasks.index') }}" class="link text-sm">Ver todas</a></div>
            <ul class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($myTasks as $task)
                    <li class="flex items-start justify-between gap-3 py-3">
                        <div>
                            <p class="text-sm font-medium">{{ $task->title }}</p>
                            <a href="{{ route('panel.projects.show', $task->project) }}" class="text-xs text-slate-500 hover:text-electric">{{ $task->project->name }}</a>
                        </div>
                        <span @class(['text-xs whitespace-nowrap', 'text-red-600 font-medium' => $task->due_date?->isPast()])>{{ $task->due_date?->format('d/m') ?? 'Sin fecha' }}</span>
                    </li>
                @empty
                    <li class="py-3 text-sm text-slate-500">No tienes tareas pendientes.</li>
                @endforelse
            </ul>
        </section>
    </div>
</x-layouts.panel>
