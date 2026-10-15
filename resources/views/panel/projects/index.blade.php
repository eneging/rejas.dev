@php use App\Models\Project; use App\Support\Permissions as P; @endphp
<x-layouts.panel title="Proyectos">
    <x-slot:actions>
        <a href="{{ route('panel.projects.export', request()->query()) }}" class="btn-secondary">Exportar (Excel CSV)</a>
        @can(P::PROJECTS_MANAGE)<a href="{{ route('panel.projects.create') }}" class="btn-primary">Nuevo proyecto</a>@endcan
    </x-slot:actions>

    <form class="card grid gap-3 sm:grid-cols-3 lg:grid-cols-7">
        <select name="cliente" class="input" aria-label="Cliente"><option value="">Cliente</option>@foreach ($clients as $c)<option value="{{ $c->id }}" @selected(request('cliente') == $c->id)>{{ $c->displayName() }}</option>@endforeach</select>
        <select name="responsable" class="input" aria-label="Responsable"><option value="">Responsable</option>@foreach ($users as $u)<option value="{{ $u->id }}" @selected(request('responsable') == $u->id)>{{ $u->name }}</option>@endforeach</select>
        <select name="estado" class="input" aria-label="Estado"><option value="">Activos</option>@foreach (Project::STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('estado') === $k)>{{ $v }}</option>@endforeach</select>
        <select name="semaforo" class="input" aria-label="Semáforo"><option value="">Semáforo</option>@foreach (['verde' => 'En plazo', 'amarillo' => 'Por vencer', 'rojo' => 'Vencido'] as $k => $v)<option value="{{ $k }}" @selected(request('semaforo') === $k)>{{ $v }}</option>@endforeach</select>
        <input type="date" name="desde" value="{{ request('desde') }}" class="input" aria-label="Entrega desde">
        <input type="date" name="hasta" value="{{ request('hasta') }}" class="input" aria-label="Entrega hasta">
        <button class="btn-secondary">Filtrar</button>
    </form>

    <div class="card overflow-x-auto p-0">
        <table class="table">
            <thead>
                <tr><th>Proyecto</th><th>Cliente</th><th>Responsables</th><th>Entrega interna</th><th>Entrega contractual</th><th>Avance</th><th>Estado</th><th>Semáforo</th>@can(P::FINANCE_VIEW)<th>Cobro</th>@endcan</tr>
            </thead>
            <tbody>
            @forelse ($projects as $project)
                <tr>
                    <td><a href="{{ route('panel.projects.show', $project) }}" class="font-medium hover:text-electric">{{ $project->name }}</a><p class="text-xs text-slate-500">{{ $project->code }}</p></td>
                    <td>{{ $project->client->displayName() }}</td>
                    <td class="text-xs">{{ $project->members->pluck('name')->join(', ') ?: '—' }}</td>
                    <td class="whitespace-nowrap">{{ $project->internal_due_date?->format('d/m/Y') ?? '—' }}</td>
                    <td class="whitespace-nowrap">{{ $project->contract_due_date?->format('d/m/Y') ?? '—' }}</td>
                    <td><x-progress-bar :value="$project->progress()" /></td>
                    <td><x-status-badge :status="$project->status" :label="$project->statusLabel()" /></td>
                    <td><x-traffic-light :project="$project" /></td>
                    @can(P::FINANCE_VIEW)<td><x-payment-status :project="$project" /></td>@endcan
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-slate-500">No hay proyectos con estos filtros.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.panel>
