<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Project;
use App\Models\Quote;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\DemoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Recorre todas las pantallas del panel con cada rol: nada debe romperse y los accesos deben respetar permisos. */
class PanelSmokeTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{string, list<string>}> */
    public static function roles(): array
    {
        $all = ['dashboard', 'leads', 'lead', 'clients', 'client', 'client.create', 'quotes', 'quote', 'quote.create', 'quote.print', 'projects', 'project', 'project.create', 'tasks', 'calendar', 'calendar.week', 'finance', 'subscriptions', 'subscription', 'subscription.create', 'portfolio', 'portfolio.create', 'users', 'user.create', 'audit'];

        return [
            'administrador' => [Role::ADMIN, $all],
            'secretaria' => [Role::SECRETARY, array_values(array_diff($all, ['users', 'user.create', 'audit']))],
            'trabajador' => [Role::WORKER, ['dashboard', 'projects', 'project', 'tasks', 'calendar', 'calendar.week']],
        ];
    }

    #[DataProvider('roles')]
    public function test_panel_pages_render_and_respect_permissions(string $role, array $allowed): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, CatalogSeeder::class, DemoSeeder::class]);
        $user = $role === Role::WORKER ? User::where('email', 'dev@eduarrejas.com')->first() : $this->userWithRole($role);

        $project = Project::first();
        $pages = [
            'dashboard' => route('panel.dashboard'),
            'leads' => route('panel.leads.index'),
            'lead' => route('panel.leads.show', Lead::first()),
            'clients' => route('panel.clients.index'),
            'client' => route('panel.clients.show', $project->client),
            'client.create' => route('panel.clients.create'),
            'quotes' => route('panel.quotes.index'),
            'quote' => route('panel.quotes.show', $this->quote($project)),
            'quote.create' => route('panel.quotes.create'),
            'quote.print' => route('panel.quotes.print', $this->quote($project)),
            'projects' => route('panel.projects.index'),
            'project' => route('panel.projects.show', $project),
            'project.create' => route('panel.projects.create'),
            'tasks' => route('panel.tasks.index'),
            'calendar' => route('panel.calendar.index'),
            'calendar.week' => route('panel.calendar.index', ['vista' => 'semana']),
            'finance' => route('panel.finance.index'),
            'subscriptions' => route('panel.subscriptions.index'),
            'subscription' => route('panel.subscriptions.show', Subscription::first()),
            'subscription.create' => route('panel.subscriptions.create'),
            'portfolio' => route('panel.portfolio.index'),
            'portfolio.create' => route('panel.portfolio.create'),
            'users' => route('panel.users.index'),
            'user.create' => route('panel.users.create'),
            'audit' => route('panel.audit.index'),
        ];

        $this->actingAs($user);
        foreach ($pages as $name => $url) {
            $response = $this->get($url);
            in_array($name, $allowed, true)
                ? $response->assertOk()
                : $response->assertForbidden();
        }
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('panel.dashboard'))->assertRedirect(route('login'));
    }

    public function test_inactive_users_cannot_log_in(): void
    {
        $user = User::factory()->create(['is_active' => false, 'password' => 'secreto-123']);

        $this->post('/login', ['email' => $user->email, 'password' => 'secreto-123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    private function quote(Project $project): Quote
    {
        return Quote::firstOrCreate(['number' => 'COT-TEST-0001'], ['client_id' => $project->client_id, 'title' => 'Prueba', 'total' => 100]);
    }
}
