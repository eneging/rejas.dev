<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectTemplate;
use App\Models\Quote;
use App\Support\Audit;
use App\Support\Numbering;
use Illuminate\Support\Facades\DB;

class ProjectBuilder
{
    /**
     * Crea un proyecto. Si se indica plantilla, sus tareas y estimaciones se COPIAN:
     * cambios posteriores a la plantilla no alteran proyectos existentes.
     *
     * @param  array<string, mixed>  $data
     * @param  list<int>  $memberIds
     */
    public function create(array $data, ?ProjectTemplate $template = null, array $memberIds = [], bool $defaultPaymentPlan = true): Project
    {
        return DB::transaction(function () use ($data, $template, $memberIds, $defaultPaymentPlan) {
            $data['code'] ??= Numbering::next(Project::class, 'PRY', 'code');
            $data['project_template_id'] = $template?->id;
            $project = Project::create($data);

            if ($template) {
                foreach ($template->tasks as $i => $templateTask) {
                    $project->tasks()->create([
                        'title' => $templateTask->title,
                        'description' => $templateTask->description,
                        'estimated_hours' => $templateTask->estimated_hours,
                        'position' => $templateTask->position ?: $i,
                    ]);
                }
            }

            if ($memberIds) {
                $project->members()->sync($memberIds);
            }

            if ($defaultPaymentPlan && (float) $project->contract_amount > 0) {
                $this->createDefaultPaymentPlan($project);
            }

            Audit::record('proyecto.creado', $project, ['codigo' => $project->code]);

            return $project;
        });
    }

    /** Plan por defecto: 50 % de adelanto al inicio y 50 % a la entrega contractual. */
    public function createDefaultPaymentPlan(Project $project): void
    {
        $amount = (float) $project->contract_amount;
        $first = round($amount / 2, 2);

        $project->paymentSchedules()->createMany([
            ['label' => 'Adelanto (50 %)', 'percentage' => 50, 'amount' => $first, 'due_date' => $project->start_date, 'position' => 0],
            ['label' => 'Pago final (50 %)', 'percentage' => 50, 'amount' => $amount - $first, 'due_date' => $project->contract_due_date, 'position' => 1],
        ]);
    }

    /** @param array<string, mixed> $overrides */
    public function fromQuote(Quote $quote, array $overrides = []): Project
    {
        $start = now()->startOfDay();
        $contractDue = $quote->delivery_days ? $start->copy()->addDays($quote->delivery_days) : null;
        $margin = (int) ($overrides['planning_margin_pct'] ?? 25);

        return $this->create(array_merge([
            'name' => $quote->title,
            'client_id' => $quote->client_id,
            'quote_id' => $quote->id,
            'contract_id' => $quote->contract?->id,
            'description' => $quote->scope,
            'start_date' => $start,
            'contract_due_date' => $contractDue,
            'internal_due_date' => $contractDue ? Project::suggestInternalDueDate($start, $contractDue, $margin) : null,
            'planning_margin_pct' => $margin,
            'currency' => $quote->currency,
            'contract_amount' => $quote->total,
            'estimated_cost' => $quote->estimated_cost,
        ], $overrides));
    }
}
