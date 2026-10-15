<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Subscription;
use App\Services\SubscriptionBilling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** Oferta del sistema para talleres (plan de ventas). */
class WorkshopOfferTest extends TestCase
{
    use RefreshDatabase;

    public function test_workshop_page_shows_monthly_and_annual_prices(): void
    {
        $this->get(route('workshop'))->assertOk()
            ->assertSeeInOrder(['Básico', 'S/99', 'S/990', 'Pro', 'S/179', 'S/1,790', 'Premium', 'S/299', 'S/2,990'])
            ->assertSee('Probar 30 días gratis')
            ->assertSee('gratis al contratar')
            ->assertDontSee('IGV')
            ->assertDontSee('Calle Autocenter');
    }

    public function test_home_shows_workshop_plans_instead_of_invented_prices(): void
    {
        $this->get('/')->assertOk()->assertSee('S/179')->assertDontSee('S/ 1,500')->assertDontSee('S/ 6,000');
    }

    public function test_tax_note_follows_configuration(): void
    {
        config(['rejas.tax_display' => 'incluido']);
        $this->get(route('workshop'))->assertSee('Incluye IGV');

        config(['rejas.tax_display' => 'adicional']);
        $this->get(route('workshop'))->assertSee('+ IGV');
    }

    public function test_testimonial_is_hidden_until_enabled(): void
    {
        config(['rejas.workshop.testimonial.enabled' => true, 'rejas.workshop.testimonial.quote' => 'Ahora sé cuánto gano.']);

        $this->get(route('workshop'))->assertSee('Calle Autocenter')->assertSee('Ahora sé cuánto gano.');
    }

    public function test_trial_request_stores_plan_of_interest(): void
    {
        $this->from(route('workshop'))->post(route('inquiry.store'), [
            'name' => 'Juan', 'email' => 'juan@taller.pe', 'company' => 'Taller Juan', 'service_type' => 'taller',
            'plan' => 'pro', 'description' => 'Taller en Parcona, 40 carros al mes.', 'privacy' => '1',
        ])->assertRedirect(route('workshop'))->assertSessionHas('status');

        $this->assertSame('pro', Lead::sole()->plan_interest);
    }

    public function test_invalid_plan_is_rejected(): void
    {
        $this->post(route('inquiry.store'), [
            'name' => 'Juan', 'email' => 'juan@taller.pe', 'service_type' => 'taller',
            'plan' => 'gratis', 'description' => 'Quiero el sistema.', 'privacy' => '1',
        ])->assertSessionHasErrors('plan');
    }

    public function test_annual_subscription_generates_one_charge_per_year(): void
    {
        $start = Carbon::parse('2025-03-10');
        $subscription = Subscription::factory()->create([
            'periodicity' => 'anual', 'amount' => 1790, 'starts_at' => $start, 'next_due_date' => $start,
        ]);

        $this->assertSame(2, app(SubscriptionBilling::class)->generateDueCharges(Carbon::parse('2026-10-15')));
        $this->assertSame(['2025-03-10', '2026-03-10'], $subscription->charges()->reorder('period_start')->get()->map(fn ($c) => $c->period_start->toDateString())->all());
        $this->assertSame('2027-03-10', $subscription->fresh()->next_due_date->toDateString());
    }
}
