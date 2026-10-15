<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Support\Audit;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $canManage = $user->can(Permissions::TASKS_MANAGE);
        $assignee = $canManage ? $request->query('responsable', $user->id) : $user->id;

        $tasks = Task::query()
            ->whereHas('project', fn ($q) => $q->visibleTo($user))
            ->when($assignee !== 'todos', fn ($q) => $q->where('assignee_id', $assignee))
            ->when($request->query('estado'), fn ($q, $s) => $q->where('status', $s), fn ($q) => $q->whereIn('status', [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS]))
            ->with(['project.client', 'assignee'])
            ->orderByRaw('due_date is null, due_date')
            ->paginate(30)
            ->withQueryString();

        return view('panel.tasks.index', [
            'tasks' => $tasks,
            'canManage' => $canManage,
            'assignee' => $assignee,
            'users' => $canManage ? User::where('is_active', true)->orderBy('name')->get() : collect(),
        ]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        abort_unless($request->user()->can(Permissions::TASKS_MANAGE), 403);

        $task = $project->tasks()->create($this->validated($request) + [
            'position' => (int) $project->tasks()->max('position') + 1,
        ]);
        Audit::record('tarea.creada', $task, ['titulo' => $task->title, 'estimacion' => $task->estimated_hours]);

        return back()->with('status', 'Tarea agregada.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $task->update($this->validated($request));
        // Queda trazado cualquier cambio de estimación/responsable/fecha que afecte el avance o la planificación.
        Audit::changes('tarea.actualizada', $task);

        return back()->with('status', 'Tarea actualizada.');
    }

    /** Completar o reabrir no requiere aprobación: el avance del proyecto se recalcula de inmediato. */
    public function status(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('work', $task);
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(Task::STATUSES))]]);

        if ($data['status'] === Task::STATUS_CANCELLED) {
            $this->authorize('update', $task);
        }

        $before = $task->status;
        $task->transitionTo($data['status']);
        Audit::record('tarea.estado', $task, ['estado' => ['antes' => $before, 'despues' => $task->status]]);

        $progress = $task->project->progress();

        return back()->with('status', 'Estado actualizado. Avance del proyecto: '.($progress === null ? 'sin planificación' : $progress.' %').'.');
    }

    public function comment(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('work', $task);
        $data = $request->validate([
            'body' => ['required_without:file', 'nullable', 'string', 'max:5000'],
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        if (! empty($data['body'])) {
            $task->comments()->create(['user_id' => $request->user()->id, 'body' => $data['body']]);
        }

        if ($file = $request->file('file')) {
            // Adjuntos en disco privado; se sirven solo mediante enlaces temporales autorizados.
            $task->attachments()->create([
                'disk' => 'local',
                'path' => $file->store("tareas/{$task->id}", 'local'),
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'uploaded_by' => $request->user()->id,
            ]);
        }

        return back()->with('status', 'Comentario registrado.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'priority' => ['required', Rule::in(array_keys(Task::PRIORITIES))],
            'estimated_hours' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'due_date' => ['nullable', 'date'],
        ]);
    }
}
