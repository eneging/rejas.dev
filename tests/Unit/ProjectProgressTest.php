<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProjectProgressTest extends TestCase
{
    private function project(array $tasks, array $attributes = []): Project
    {
        $project = new Project($attributes);
        $project->setRelation('tasks', collect($tasks)->map(fn ($t) => new Task($t)));

        return $project;
    }

    public function test_without_computable_tasks_progress_is_null_not_zero(): void
    {
        $this->assertNull($this->project([])->progress());
        $this->assertNull($this->project([['status' => 'cancelada']])->progress());
    }

    public function test_weights_by_estimated_hours_when_all_tasks_have_them(): void
    {
        $project = $this->project([
            ['status' => 'completada', 'estimated_hours' => 6],
            ['status' => 'pendiente', 'estimated_hours' => 2],
            ['status' => 'cancelada', 'estimated_hours' => 100],
        ]);

        $this->assertSame(75.0, $project->progress());
    }

    public function test_uses_weight_one_when_any_estimate_is_missing(): void
    {
        $project = $this->project([
            ['status' => 'completada', 'estimated_hours' => 6],
            ['status' => 'pendiente', 'estimated_hours' => null],
            ['status' => 'en_progreso', 'estimated_hours' => 2],
            ['status' => 'completada', 'estimated_hours' => 1],
        ]);

        $this->assertSame(50.0, $project->progress());
    }

    /** @return array<string, array{string, int|null, string}> */
    public static function lights(): array
    {
        return [
            'en plazo' => ['en_ejecucion', 6, 'verde'],
            'faltan 5 días' => ['en_ejecucion', 5, 'amarillo'],
            'vence hoy' => ['en_ejecucion', 0, 'amarillo'],
            'vencido' => ['en_ejecucion', -1, 'rojo'],
            'finalizado aunque vencido' => ['finalizado', -10, 'finalizado'],
            'entregado' => ['entregado', -3, 'finalizado'],
            'cancelado' => ['cancelado', 3, 'cancelado'],
            'sin fecha' => ['en_ejecucion', null, 'sin_fecha'],
        ];
    }

    #[DataProvider('lights')]
    public function test_traffic_light(string $status, ?int $days, string $expected): void
    {
        $today = Carbon::parse('2026-10-15');
        $project = $this->project([], [
            'status' => $status,
            'internal_due_date' => $days === null ? null : $today->copy()->addDays($days),
        ]);

        $this->assertSame($expected, $project->trafficLight($today));
    }

    public function test_traffic_light_falls_back_to_contract_date(): void
    {
        $today = Carbon::parse('2026-10-15');
        $project = $this->project([], ['status' => 'en_ejecucion', 'contract_due_date' => $today->copy()->subDay()]);

        $this->assertSame('rojo', $project->trafficLight($today));
    }

    public function test_suggested_internal_date_reserves_planning_margin(): void
    {
        $start = Carbon::parse('2026-10-01');

        $this->assertSame('2026-10-16', Project::suggestInternalDueDate($start, $start->copy()->addDays(20), 25)->toDateString());
    }
}
