<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Lead;
use App\Models\PortfolioProject;
use App\Models\ProjectTemplate;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Task;
use App\Models\User;
use App\Services\ProjectBuilder;
use App\Services\SubscriptionBilling;
use Illuminate\Database\Seeder;

/** Datos de demostración para desarrollo local (no ejecutar en producción). */
class DemoSeeder extends Seeder
{
    public function run(ProjectBuilder $builder, SubscriptionBilling $billing): void
    {
        $secretary = User::firstOrCreate(['email' => 'secretaria@eduarrejas.com'], ['name' => 'Ana Secretaría', 'password' => 'password']);
        $secretary->roles()->syncWithoutDetaching(Role::where('slug', Role::SECRETARY)->pluck('id'));
        $worker = User::firstOrCreate(['email' => 'dev@eduarrejas.com'], ['name' => 'Luis Desarrollador', 'password' => 'password']);
        $worker->roles()->syncWithoutDetaching(Role::where('slug', Role::WORKER)->pluck('id'));

        $taller = Client::firstOrCreate(['name' => 'Carlos Pérez'], ['business_name' => 'Taller Mecánico Pérez SAC', 'email' => 'carlos@tallerperez.pe', 'phone' => '987654321', 'sector' => 'Talleres']);
        $colegio = Client::firstOrCreate(['name' => 'María Torres'], ['business_name' => 'I.E.P. San Martín', 'email' => 'direccion@sanmartin.edu.pe', 'sector' => 'Educación']);

        if (! $taller->projects()->exists()) {
            $p = $builder->create([
                'name' => 'Sistema de inventario de repuestos', 'client_id' => $taller->id,
                'start_date' => now()->subDays(20), 'contract_due_date' => now()->addDays(10), 'internal_due_date' => now()->addDays(3),
                'planning_margin_pct' => 25, 'currency' => 'PEN', 'contract_amount' => 8000, 'estimated_cost' => 3500, 'status' => 'en_ejecucion',
            ], ProjectTemplate::where('service_type', 'inventario')->first(), [$worker->id, $secretary->id]);
            $p->tasks()->limit(4)->get()->each(fn (Task $t) => $t->transitionTo(Task::STATUS_COMPLETED));
            $p->tasks()->update(['assignee_id' => $worker->id]);
            $p->payments()->create(['client_id' => $taller->id, 'payment_schedule_id' => $p->paymentSchedules()->first()->id, 'paid_at' => now()->subDays(19), 'amount' => 4000, 'method' => 'transferencia', 'reference' => 'OP-102938']);

            $billing->generateDueCharges();
            Subscription::create(['client_id' => $taller->id, 'service' => 'Sistema para talleres · Plan Pro', 'amount' => 179, 'subscription_plan_id' => SubscriptionPlan::where('name', 'Talleres Pro')->value('id'), 'starts_at' => now()->subMonths(2)->startOfMonth(), 'next_due_date' => now()->subMonths(2)->startOfMonth()]);
            $billing->generateDueCharges();
        }

        if (! $colegio->projects()->exists()) {
            $builder->create([
                'name' => 'Plataforma de gestión escolar', 'client_id' => $colegio->id,
                'start_date' => now()->subDays(5), 'contract_due_date' => now()->addDays(55), 'planning_margin_pct' => 25,
                'internal_due_date' => now()->addDays(40), 'currency' => 'PEN', 'contract_amount' => 15000, 'status' => 'en_ejecucion',
            ], ProjectTemplate::where('service_type', 'escolar')->first(), [$worker->id]);
        }

        Lead::firstOrCreate(['email' => 'gerencia@ferreteriasol.pe'], [
            'name' => 'Rosa Quispe', 'company' => 'Ferretería El Sol', 'phone' => '912345678', 'service_type' => 'inventario',
            'description' => 'Necesitamos controlar el stock de tres tiendas y saber qué reponer cada semana.',
        ]);

        PortfolioProject::firstOrCreate(['slug' => 'inventario-taller-perez'], [
            'title' => 'Inventario inteligente para un taller mecánico', 'sector' => 'Talleres',
            'summary' => 'Control de repuestos, órdenes de trabajo y alertas de reposición para un taller en Lima.',
            'challenge' => 'El taller perdía ventas por no saber qué repuestos tenía en stock.',
            'solution' => 'Un sistema web con kardex, alertas de stock mínimo y órdenes de trabajo vinculadas al inventario.',
            'results' => 'Reducción de quiebres de stock y mayor rapidez en la atención.',
            'technologies' => ['Laravel', 'MySQL', 'Tailwind CSS'], 'is_published' => true,
        ]);
    }
}
