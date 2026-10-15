<?php

namespace Tests\Feature;

use App\Models\CalendarEvent;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\PortfolioProject;
use App\Models\Project;
use App\Models\ProjectTemplate;
use App\Models\Quote;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Task;
use App\Services\ProjectBuilder;
use App\Services\SubscriptionBilling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Criterios mínimos de aceptación (documento maestro §15). */
class AcceptanceCriteriaTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_inquiry_creates_lead_with_privacy_consent_evidence(): void
    {
        $this->post('/cotizar', [
            'name' => 'Rosa Quispe',
            'email' => 'rosa@example.com',
            'service_type' => 'inventario',
            'description' => 'Necesito controlar el stock de mis tiendas.',
            'privacy' => '1',
        ])->assertRedirect(route('inquiry.create'));

        $lead = Lead::sole();
        $this->assertSame('nuevo', $lead->status);
        $privacy = $lead->consents()->where('type', 'privacy_policy')->sole();
        $this->assertTrue($privacy->accepted);
        $this->assertSame(config('rejas.legal.privacy_version'), $privacy->document_version);
        $this->assertNotNull($privacy->ip_address);
        $this->assertFalse($lead->consents()->where('type', 'marketing')->sole()->accepted);
    }

    public function test_web_inquiry_requires_privacy_acceptance(): void
    {
        $this->post('/cotizar', [
            'name' => 'Rosa', 'email' => 'rosa@example.com', 'service_type' => 'web', 'description' => 'Quiero una web nueva.',
        ])->assertSessionHasErrors('privacy');

        $this->assertSame(0, Lead::count());
    }

    public function test_secretary_sends_quotes_and_records_payments_without_seeing_margins(): void
    {
        $secretary = $this->userWithRole(Role::SECRETARY);
        $quote = Quote::factory()->create(['estimated_cost' => 1234.56]);
        $quote->items()->create(['description' => 'Desarrollo', 'quantity' => 1, 'unit_price' => 5000, 'total' => 5000]);

        $this->actingAs($secretary)->post(route('panel.quotes.send', $quote), ['channel' => 'correo'])->assertRedirect();
        $this->assertSame('enviada', $quote->fresh()->status);
        $this->assertSame(1, $quote->versions()->count());

        $this->get(route('panel.quotes.show', $quote))->assertOk()->assertDontSee('Costo estimado')->assertDontSee('1,234.56');

        $project = Project::factory()->create(['estimated_cost' => 4000]);
        $this->post(route('panel.payments.store', $project), [
            'paid_at' => now()->toDateString(), 'amount' => 5000, 'method' => 'transferencia',
        ])->assertRedirect();
        $this->assertSame(5000.0, $project->amountPaid());

        $this->get(route('panel.finance.index'))->assertOk()->assertDontSee('Rentabilidad estimada');
        $this->get(route('panel.projects.show', $project))->assertOk()->assertDontSee('Utilidad estimada');
        $this->post(route('panel.finance.expenses.store'), ['description' => 'x', 'spent_at' => now()->toDateString(), 'amount' => 10, 'currency' => 'PEN'])->assertForbidden();
    }

    public function test_project_supports_several_workers_with_individual_tasks(): void
    {
        $admin = $this->userWithRole(Role::ADMIN);
        [$ana, $luis] = [$this->userWithRole(Role::WORKER), $this->userWithRole(Role::WORKER)];
        $project = Project::factory()->create();

        $this->actingAs($admin)->put(route('panel.projects.members', $project), ['members' => [$ana->id, $luis->id]]);
        $this->post(route('panel.tasks.store', $project), ['title' => 'Frontend', 'assignee_id' => $ana->id, 'priority' => 'alta']);
        $this->post(route('panel.tasks.store', $project), ['title' => 'Backend', 'assignee_id' => $luis->id, 'priority' => 'media']);

        $this->assertEqualsCanonicalizing([$ana->id, $luis->id], $project->members()->pluck('users.id')->all());
        $this->assertSame(['Frontend' => $ana->id, 'Backend' => $luis->id], $project->tasks()->pluck('assignee_id', 'title')->all());
    }

    public function test_completing_and_reopening_a_task_recalculates_progress_without_approval(): void
    {
        $worker = $this->userWithRole(Role::WORKER);
        $project = Project::factory()->create();
        $task = Task::factory()->for($project)->create(['assignee_id' => $worker->id, 'estimated_hours' => 3]);
        Task::factory()->for($project)->create(['estimated_hours' => 1]);

        $this->assertSame(0.0, $project->progress());

        $this->actingAs($worker)->patch(route('panel.tasks.status', $task), ['status' => 'completada'])->assertRedirect();
        $this->assertSame(75.0, $project->fresh()->progress());

        $this->patch(route('panel.tasks.status', $task), ['status' => 'pendiente'])->assertRedirect();
        $this->assertSame(0.0, $project->fresh()->progress());
    }

    public function test_worker_cannot_change_tasks_assigned_to_others(): void
    {
        $worker = $this->userWithRole(Role::WORKER);
        $task = Task::factory()->create(['assignee_id' => $this->userWithRole(Role::WORKER)->id]);

        $this->actingAs($worker)->patch(route('panel.tasks.status', $task), ['status' => 'completada'])->assertForbidden();
    }

    public function test_traffic_light_ignores_payments(): void
    {
        $project = Project::factory()->create(['internal_due_date' => now()->addDays(15)]);
        $project->paymentSchedules()->create(['label' => 'Adelanto', 'amount' => 5000, 'due_date' => now()->subDays(5)]);

        $this->assertSame('vencido', $project->paymentStatus());
        $this->assertSame('verde', $project->trafficLight());
    }

    public function test_secretary_can_change_internal_dates_but_not_contract_date(): void
    {
        $secretary = $this->userWithRole(Role::SECRETARY);
        $project = Project::factory()->create(['contract_due_date' => '2026-12-31']);

        $this->actingAs($secretary)->patch(route('panel.projects.update', $project), ['internal_due_date' => '2026-12-10'])->assertRedirect();
        $this->assertSame('2026-12-10', $project->fresh()->internal_due_date->toDateString());

        $this->patch(route('panel.projects.update', $project), ['contract_due_date' => '2027-01-31'])->assertForbidden();
        $this->assertSame('2026-12-31', $project->fresh()->contract_due_date->toDateString());

        $admin = $this->userWithRole(Role::ADMIN);
        $this->actingAs($admin)->patch(route('panel.projects.update', $project), ['contract_due_date' => '2027-01-31'])->assertRedirect();
        $this->assertSame('2027-01-31', $project->fresh()->contract_due_date->toDateString());
        $this->assertDatabaseHas('audit_logs', ['action' => 'proyecto.actualizado', 'auditable_id' => $project->id, 'user_id' => $admin->id]);
    }

    public function test_project_table_and_calendar_export_with_filters(): void
    {
        $admin = $this->userWithRole(Role::ADMIN);
        $a = Project::factory()->create(['name' => 'Proyecto Alfa']);
        Project::factory()->create(['name' => 'Proyecto Beta']);

        $csv = $this->actingAs($admin)->get(route('panel.projects.export', ['cliente' => $a->client_id]))->assertOk()->streamedContent();
        $this->assertStringContainsString('Proyecto Alfa', $csv);
        $this->assertStringNotContainsString('Proyecto Beta', $csv);
        $this->assertStringContainsString('America/Lima', $csv);
        $this->assertStringContainsString('Filtro;Cliente', $csv);

        $csv = $this->get(route('panel.calendar.export', ['desde' => now()->startOfYear()->toDateString(), 'hasta' => now()->addYear()->toDateString(), 'tipo' => 'entrega']))->assertOk()->streamedContent();
        $this->assertStringContainsString('Entrega contractual · Proyecto Alfa', $csv);
    }

    public function test_overdue_subscription_stays_pending_until_payment_is_recorded(): void
    {
        $subscription = Subscription::factory()->create(['next_due_date' => now()->subMonths(2)->startOfMonth(), 'amount' => 300]);
        $billing = app(SubscriptionBilling::class);

        $this->assertSame(3, $billing->generateDueCharges());
        $this->assertSame(0, $billing->generateDueCharges(), 'Generar dos veces no duplica cargos');
        $this->assertSame(3, $subscription->charges()->where('status', 'por_cobrar')->count());
        $this->assertSame(0, Payment::count(), 'Vencer no es cobrar');

        $secretary = $this->userWithRole(Role::SECRETARY);
        $charge = $subscription->charges()->orderBy('period_start')->first();
        $this->actingAs($secretary)->post(route('panel.subscriptions.pay', $charge), [
            'paid_at' => now()->toDateString(), 'amount' => 300, 'method' => 'yape',
        ])->assertRedirect();

        $this->assertSame('cobrado', $charge->fresh()->status);
        $this->assertSame(2, $subscription->charges()->where('status', 'por_cobrar')->count());
    }

    public function test_portfolio_is_server_rendered_and_works_without_javascript(): void
    {
        PortfolioProject::create(['slug' => 'caso', 'title' => 'Caso publicado', 'sector' => 'Talleres', 'summary' => 'Resumen', 'is_published' => true]);
        PortfolioProject::create(['slug' => 'oculto', 'title' => 'Caso borrador', 'sector' => 'X', 'summary' => 'Resumen', 'is_published' => false]);

        $this->get('/')->assertOk()
            ->assertSee('Desarrollamos tecnología que')
            ->assertSee('Explorar proyectos')
            ->assertSee('Solicitar cotización')
            ->assertSee('Caso publicado')
            ->assertDontSee('Caso borrador');
        $this->get('/portafolio/caso')->assertOk();
        $this->get('/portafolio/oculto')->assertNotFound();
    }

    public function test_quote_acceptance_requires_evidence_and_creates_contract_and_project(): void
    {
        $secretary = $this->userWithRole(Role::SECRETARY);
        $quote = Quote::factory()->sent()->create(['total' => 6000]);

        $this->actingAs($secretary)->post(route('panel.quotes.accept', $quote), ['channel' => 'correo', 'evidence' => ''])
            ->assertSessionHasErrors('evidence');
        $this->assertSame('enviada', $quote->fresh()->status);

        $this->post(route('panel.quotes.accept', $quote), ['channel' => 'correo', 'evidence' => 'Correo del cliente aceptando v1', 'create_project' => '1'])->assertRedirect();

        $quote->refresh();
        $this->assertSame('aceptada', $quote->status);
        $this->assertNotNull($quote->contract);
        $project = $quote->project;
        $this->assertSame(6000.0, (float) $project->contract_amount);
        $this->assertSame(2, $project->paymentSchedules()->count());
        $this->assertTrue($project->internal_due_date->lt($project->contract_due_date));
    }

    public function test_template_changes_do_not_alter_existing_projects(): void
    {
        $template = ProjectTemplate::create(['name' => 'Web', 'service_type' => 'web']);
        $template->tasks()->create(['title' => 'Diseño', 'estimated_hours' => 5]);
        $project = app(ProjectBuilder::class)->create(Project::factory()->raw(['code' => null]), $template->load('tasks'));

        $template->tasks()->update(['title' => 'Cambiado', 'estimated_hours' => 50]);
        $template->tasks()->create(['title' => 'Nueva']);

        $this->assertSame(['Diseño'], $project->tasks()->pluck('title')->all());
        $this->assertSame(5.0, (float) $project->tasks()->first()->estimated_hours);
    }

    public function test_worker_only_sees_own_projects_and_no_finance(): void
    {
        $worker = $this->userWithRole(Role::WORKER);
        $mine = Project::factory()->create(['name' => 'Mi proyecto']);
        $mine->members()->attach($worker);
        $other = Project::factory()->create(['name' => 'Ajeno']);

        $this->actingAs($worker)->get(route('panel.projects.index'))->assertOk()->assertSee('Mi proyecto')->assertDontSee('Ajeno');
        $this->get(route('panel.projects.show', $other))->assertForbidden();
        $this->get(route('panel.finance.index'))->assertForbidden();
        $this->get(route('panel.projects.show', $mine))->assertOk()->assertDontSee('Plan de pagos');
    }

    public function test_private_calendar_events_are_hidden_from_other_users(): void
    {
        $ana = $this->userWithRole(Role::ADMIN);
        $luis = $this->userWithRole(Role::ADMIN);
        CalendarEvent::create(['user_id' => $ana->id, 'title' => 'Cita médica', 'type' => 'recordatorio', 'starts_at' => now(), 'is_private' => true]);

        $this->actingAs($ana)->get(route('panel.calendar.index'))->assertSee('Cita médica');
        $this->actingAs($luis)->get(route('panel.calendar.index'))->assertDontSee('Cita médica');
    }
}
