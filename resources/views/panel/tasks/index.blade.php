@php use App\Models\Task; @endphp
<x-layouts.panel title="Tareas">
    <form class="flex flex-wrap gap-3">
        @if ($canManage)
            <select name="responsable" class="input w-auto" aria-label="Responsable"><option value="todos" @selected($assignee === 'todos')>Todos</option>@foreach ($users as $u)<option value="{{ $u->id }}" @selected((string) $assignee === (string) $u->id)>{{ $u->name }}</option>@endforeach</select>
        @endif
        <select name="estado" class="input w-auto" aria-label="Estado"><option value="">Abiertas</option>@foreach (Task::STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('estado') === $k)>{{ $v }}</option>@endforeach</select>
        <button class="btn-secondary">Filtrar</button>
    </form>
    <div class="card overflow-x-auto p-0">
        <table class="table">
            <thead><tr><th></th><th>Tarea</th><th>Proyecto</th><th>Responsable</th><th>Prioridad</th><th>Fecha</th><th>Estado</th></tr></thead>
            <tbody>
            @forelse ($tasks as $task)
                <tr>
                    <td class="w-8">
                        @can('work', $task)
                            <form method="POST" action="{{ route('panel.tasks.status', $task) }}">@csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ $task->isCompleted() ? Task::STATUS_PENDING : Task::STATUS_COMPLETED }}">
                                <button title="{{ $task->isCompleted() ? 'Reabrir' : 'Completar' }}" @class(['grid h-5 w-5 place-items-center rounded border text-xs', 'border-electric bg-electric text-white' => $task->isCompleted(), 'border-slate-300 hover:border-electric' => ! $task->isCompleted()])>{{ $task->isCompleted() ? '✓' : '' }}</button>
                            </form>
                        @endcan
                    </td>
                    <td class="font-medium">{{ $task->title }}</td>
                    <td><a href="{{ route('panel.projects.show', $task->project) }}" class="hover:text-electric">{{ $task->project->name }}</a><p class="text-xs text-slate-500">{{ $task->project->client->displayName() }}</p></td>
                    <td>{{ $task->assignee?->name ?? '—' }}</td>
                    <td>{{ Task::PRIORITIES[$task->priority] ?? $task->priority }}</td>
                    <td @class(['whitespace-nowrap', 'text-red-600 font-medium' => $task->due_date?->isPast() && ! $task->isCompleted()])>{{ $task->due_date?->format('d/m/Y') ?? '—' }}</td>
                    <td><x-status-badge :status="$task->status" :label="$task->statusLabel()" /></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-slate-500">Sin tareas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $tasks->links() }}
</x-layouts.panel>
