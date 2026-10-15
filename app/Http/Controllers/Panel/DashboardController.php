<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Quote;
use App\Models\SubscriptionCharge;
use App\Models\Task;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $projects = Project::visibleTo($user)
            ->whereNotIn('status', ['finalizado', 'cancelado'])
            ->with(['client', 'tasks', 'payments', 'paymentSchedules'])
            ->orderBy('internal_due_date')
            ->get();

        $data = [
            'projects' => $projects,
            'lights' => $projects->countBy(fn (Project $p) => $p->trafficLight()),
            'myTasks' => Task::where('assignee_id', $user->id)
                ->whereIn('status', [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS])
                ->with('project')
                ->orderByRaw('due_date is null, due_date')
                ->limit(8)
                ->get(),
        ];

        if ($user->can(Permissions::LEADS_MANAGE)) {
            $data['newLeads'] = Lead::where('status', 'nuevo')->count();
            $data['openQuotes'] = Quote::where('status', 'enviada')->count();
        }

        if ($user->can(Permissions::FINANCE_VIEW)) {
            $data['receivable'] = $projects->sum(fn (Project $p) => $p->balance());
            $data['pendingCharges'] = SubscriptionCharge::where('status', 'por_cobrar')->sum('amount');
        }

        return view('panel.dashboard', $data);
    }
}
