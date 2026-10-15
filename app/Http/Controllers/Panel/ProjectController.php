<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectTemplate;
use App\Models\Task;
use App\Models\User;
use App\Services\ProjectBuilder;
use App\Support\Audit;
use App\Support\CsvExport;
use App\Support\Permissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        return view('panel.projects.index', [
            'projects' => $this->filtered($request),
            'clients' => Client::orderBy('name')->get(['id', 'name', 'business_name']),
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $showFinance = $request->user()->can(Permissions::FINANCE_VIEW);
        $rows = $this->filtered($request)->map(fn (Project $p) => array_filter([
            $p->code, $p->name, $p->client->displayName(),
            $p->members->pluck('name')->join(', '),
            $p->internal_due_date?->format('d/m/Y'), $p->contract_due_date?->format('d/m/Y'),
            $p->progress() === null ? 'Sin planificación' : $p->progress().' %',
            $p->statusLabel(), $p->trafficLight(),
            $showFinance ? $p->paymentStatus() : null,
        ], fn ($v) => $v !== null));

        $headers = ['Código', 'Proyecto', 'Cliente', 'Responsables', 'Entrega interna', 'Entrega contractual', 'Avance', 'Estado', 'Semáforo'];
        if ($showFinance) {
            $headers[] = 'Estado de cobro';
        }

        return CsvExport::download('proyectos', $headers, $rows, $this->filterSummary($request));
    }

    public function create(): View
    {
        return view('panel.projects.create', [
            'clients' => Client::orderBy('name')->get(),
            'templates' => ProjectTemplate::withCount('tasks')->orderBy('name')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, ProjectBuilder $builder): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'client_id' => ['required', 'exists:clients,id'],
            'project_template_id' => ['nullable', 'exists:project_templates,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'start_date' => ['required', 'date'],
            'contract_due_date' => ['required', 'date', 'after_or_equal:start_date'],
            'internal_due_date' => ['nullable', 'date', 'after_or_equal:start_date', 'before_or_equal:contract_due_date'],
            'planning_margin_pct' => ['required', 'integer', 'min:0', 'max:60'],
            'currency' => ['required', 'in:PEN,USD'],
            'contract_amount' => ['nullable', 'numeric', 'min:0'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'members' => ['array'],
            'members.*' => ['exists:users,id'],
        ]);

        if (! $request->user()->can(Permissions::FINANCE_VIEW_MARGINS)) {
            unset($data['estimated_cost']);
        }

        $data['internal_due_date'] ??= Project::suggestInternalDueDate(
            Carbon::parse($data['start_date']), Carbon::parse($data['contract_due_date']), (int) $data['planning_margin_pct'],
        );
        $data['contract_amount'] ??= 0;

        $template = isset($data['project_template_id']) ? ProjectTemplate::with('tasks')->find($data['project_template_id']) : null;
        $members = $data['members'] ?? [];
        unset($data['members'], $data['project_template_id']);

        $project = $builder->create($data, $template, $members);

        return redirect()->route('panel.projects.show', $project)->with('status', 'Proyecto creado.');
    }

    public function show(Request $request, Project $project): View
    {
        $this->authorize('view', $project);

        $project->load([
            'client', 'members', 'quote', 'contract', 'template',
            'tasks.assignee', 'tasks.comments.user',
            'paymentSchedules', 'payments.recorder', 'payments.schedule',
        ]);

        return view('panel.projects.show', [
            'project' => $project,
            'users' => User::where('is_active', true)->orderBy('name')->get(),
            'activity' => AuditLog::with('user')
                ->where(fn ($q) => $q->where(fn ($w) => $w->where('auditable_type', $project->getMorphClass())->where('auditable_id', $project->id))
                    ->orWhere(fn ($w) => $w->where('auditable_type', (new Task)->getMorphClass())->whereIn('auditable_id', $project->tasks->pluck('id'))))
                ->latest('created_at')->limit(15)->get(),
            'paymentMethods' => Payment::METHODS,
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:200'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status' => ['sometimes', 'required', Rule::in(array_keys(Project::STATUSES))],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'internal_due_date' => ['sometimes', 'nullable', 'date'],
            'contract_due_date' => ['sometimes', 'nullable', 'date'],
            'planning_margin_pct' => ['sometimes', 'integer', 'min:0', 'max:60'],
            'contract_amount' => ['sometimes', 'numeric', 'min:0'],
            'estimated_cost' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ]);

        // La fecha contractual solo la cambia quien tiene permiso explícito (no la secretaría).
        if (array_key_exists('contract_due_date', $data)
            && $data['contract_due_date'] !== $project->contract_due_date?->toDateString()) {
            $this->authorize('updateContractDate', $project);
        }
        if (array_key_exists('estimated_cost', $data) && ! $request->user()->can(Permissions::FINANCE_VIEW_MARGINS)) {
            unset($data['estimated_cost']);
        }

        if (isset($data['status'])) {
            $data['finished_at'] = $data['status'] === 'finalizado' ? ($project->finished_at ?? now()) : null;
        }

        $project->update($data);
        Audit::changes('proyecto.actualizado', $project);

        return back()->with('status', 'Proyecto actualizado.');
    }

    public function members(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate(['members' => ['array'], 'members.*' => ['exists:users,id']]);
        $project->members()->sync($data['members'] ?? []);
        Audit::record('proyecto.equipo_actualizado', $project, ['integrantes' => $data['members'] ?? []]);

        return back()->with('status', 'Equipo actualizado.');
    }

    /** @return Collection<int, Project> */
    private function filtered(Request $request): Collection
    {
        $projects = Project::visibleTo($request->user())
            ->with(['client', 'members', 'tasks', 'payments', 'paymentSchedules'])
            ->when($request->query('cliente'), fn (Builder $q, $id) => $q->where('client_id', $id))
            ->when($request->query('responsable'), fn (Builder $q, $id) => $q->whereHas('members', fn ($m) => $m->where('users.id', $id)))
            ->when($request->query('estado'), fn (Builder $q, $s) => $q->where('status', $s), fn (Builder $q) => $request->boolean('todos') ? $q : $q->whereNotIn('status', ['finalizado', 'cancelado']))
            ->when($request->query('desde'), fn (Builder $q, $d) => $q->whereDate('internal_due_date', '>=', $d))
            ->when($request->query('hasta'), fn (Builder $q, $d) => $q->whereDate('internal_due_date', '<=', $d))
            ->orderByRaw('internal_due_date is null, internal_due_date')
            ->get();

        if ($light = $request->query('semaforo')) {
            $projects = $projects->filter(fn (Project $p) => $p->trafficLight() === $light)->values();
        }

        return $projects;
    }

    /** @return array<string, string> */
    private function filterSummary(Request $request): array
    {
        return array_filter([
            'Cliente' => $request->query('cliente') ? Client::find($request->query('cliente'))?->displayName() : null,
            'Responsable' => $request->query('responsable') ? User::find($request->query('responsable'))?->name : null,
            'Estado' => $request->query('estado') ? Project::STATUSES[$request->query('estado')] ?? null : null,
            'Semáforo' => $request->query('semaforo'),
            'Entrega interna desde' => $request->query('desde'),
            'Entrega interna hasta' => $request->query('hasta'),
        ]);
    }
}
