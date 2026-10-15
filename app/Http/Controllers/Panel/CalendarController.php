<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\Client;
use App\Models\PaymentSchedule;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Support\CsvExport;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Calendario construido a partir de las mismas fuentes que la tabla de proyectos
 * (fechas de proyecto, tareas, hitos de cobro) más los eventos propios.
 */
class CalendarController extends Controller
{
    public const KINDS = [
        'entrega' => 'Entregas',
        'tarea' => 'Tareas',
        'cobro' => 'Cobros',
        'reunion' => 'Reuniones',
        'hito' => 'Hitos',
        'recordatorio' => 'Recordatorios',
    ];

    public function index(Request $request): View
    {
        $view = in_array($request->query('vista'), ['mes', 'semana', 'dia'], true) ? $request->query('vista') : 'mes';
        $date = Carbon::parse($request->query('fecha', now()->toDateString()));
        [$from, $to] = $this->range($view, $date);

        return view('panel.calendar.index', [
            'view' => $view,
            'date' => $date,
            'from' => $from,
            'to' => $to,
            'entries' => $this->entries($request, $from, $to)->groupBy(fn ($e) => $e['date']->toDateString()),
            'kinds' => self::KINDS,
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'clients' => Client::orderBy('name')->get(['id', 'name', 'business_name']),
            'projects' => Project::visibleTo($request->user())->orderBy('name')->get(['id', 'code', 'name']),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $from = Carbon::parse($request->query('desde', now()->startOfMonth()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->query('hasta', now()->endOfMonth()->toDateString()))->endOfDay();

        $rows = $this->entries($request, $from, $to)->map(fn ($e) => [
            $e['date']->format('d/m/Y'), $e['time'] ?? '', self::KINDS[$e['kind']] ?? $e['kind'], $e['title'], $e['project'] ?? '', $e['owner'] ?? '',
        ]);

        return CsvExport::download('calendario', ['Fecha', 'Hora', 'Tipo', 'Título', 'Proyecto', 'Responsable'], $rows, array_filter([
            'Desde' => $from->format('d/m/Y'),
            'Hasta' => $to->format('d/m/Y'),
            'Tipo' => $request->query('tipo'),
            'Responsable' => $request->query('responsable') ? User::find($request->query('responsable'))?->name : null,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'type' => ['required', 'in:'.implode(',', array_keys(CalendarEvent::TYPES))],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_private' => ['nullable', 'boolean'],
        ]);

        if (isset($data['project_id'])) {
            abort_unless(Project::visibleTo($request->user())->whereKey($data['project_id'])->exists(), 403);
        }

        CalendarEvent::create($data + ['user_id' => $request->user()->id, 'is_private' => $request->boolean('is_private')]);

        return back()->with('status', 'Evento agregado al calendario.');
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function range(string $view, Carbon $date): array
    {
        return match ($view) {
            'dia' => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
            'semana' => [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()],
            default => [$date->copy()->startOfMonth()->startOfWeek(), $date->copy()->endOfMonth()->endOfWeek()],
        };
    }

    /** @return Collection<int, array<string, mixed>> */
    private function entries(Request $request, Carbon $from, Carbon $to): Collection
    {
        $user = $request->user();
        $kind = $request->query('tipo');
        $owner = $request->query('responsable');
        $clientId = $request->query('cliente');
        $projectId = $request->query('proyecto');
        $want = fn (string $k) => ! $kind || $kind === $k;

        $projects = Project::visibleTo($user)
            ->with(['client', 'members'])
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->when($projectId, fn ($q) => $q->whereKey($projectId))
            ->get();
        $projectIds = $projects->pluck('id');
        $entries = collect();

        if ($want('entrega')) {
            foreach ($projects as $p) {
                if ($owner && ! $p->members->contains('id', (int) $owner)) {
                    continue;
                }
                foreach (['internal_due_date' => 'Entrega interna', 'contract_due_date' => 'Entrega contractual'] as $field => $label) {
                    if ($p->{$field} && $p->{$field}->between($from, $to)) {
                        $entries->push(['date' => $p->{$field}, 'kind' => 'entrega', 'title' => "$label · {$p->name}", 'project' => $p->code, 'owner' => $p->members->pluck('name')->join(', '), 'url' => route('panel.projects.show', $p)]);
                    }
                }
            }
        }

        if ($want('tarea')) {
            Task::with(['assignee', 'project'])
                ->whereIn('project_id', $projectIds)
                ->whereBetween('due_date', [$from->toDateString(), $to->toDateString()])
                ->where('status', '!=', Task::STATUS_CANCELLED)
                ->when($owner, fn ($q) => $q->where('assignee_id', $owner))
                ->get()
                ->each(fn (Task $t) => $entries->push(['date' => $t->due_date, 'kind' => 'tarea', 'title' => $t->title.($t->isCompleted() ? ' ✓' : ''), 'project' => $t->project->code, 'owner' => $t->assignee?->name, 'url' => route('panel.projects.show', $t->project_id)]));
        }

        if ($want('cobro') && $user->can(Permissions::FINANCE_VIEW) && ! $owner) {
            PaymentSchedule::with('project')
                ->whereIn('project_id', $projectIds)
                ->whereBetween('due_date', [$from->toDateString(), $to->toDateString()])
                ->get()
                ->each(fn (PaymentSchedule $s) => $entries->push(['date' => $s->due_date, 'kind' => 'cobro', 'title' => "{$s->label} · {$s->project->name}", 'project' => $s->project->code, 'owner' => null, 'url' => route('panel.projects.show', $s->project_id)]));
        }

        CalendarEvent::visibleTo($user)
            ->with(['user', 'project'])
            ->whereBetween('starts_at', [$from, $to])
            ->when($kind, fn ($q) => $q->where('type', $kind))
            ->when($owner, fn ($q) => $q->where('user_id', $owner))
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->where(fn ($q) => $q->whereNull('project_id')->orWhereIn('project_id', $projectIds))
            ->get()
            ->each(fn (CalendarEvent $e) => $entries->push(['date' => $e->starts_at, 'time' => $e->starts_at->format('H:i'), 'kind' => $e->type, 'title' => $e->title.($e->is_private ? ' (privado)' : ''), 'project' => $e->project?->code, 'owner' => $e->user->name, 'url' => $e->project_id ? route('panel.projects.show', $e->project_id) : null]));

        return $entries->sortBy(fn ($e) => $e['date']->format('Y-m-d').($e['time'] ?? '00:00'))->values();
    }
}
