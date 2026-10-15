<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionCharge;
use App\Support\Audit;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Cobro manual de suscripciones (sin pasarela de pago).
 * Al vencer un periodo se genera un cargo "por_cobrar"; solo un pago registrado lo pasa a "cobrado".
 */
class SubscriptionBilling
{
    /** Genera los cargos vencidos de las suscripciones activas. Devuelve cuántos se crearon. */
    public function generateDueCharges(?CarbonInterface $today = null): int
    {
        $today = ($today ?? now())->copy()->startOfDay();
        $created = 0;

        Subscription::where('status', 'activa')
            ->whereDate('next_due_date', '<=', $today)
            ->each(function (Subscription $subscription) use ($today, &$created) {
                DB::transaction(function () use ($subscription, $today, &$created) {
                    while ($subscription->next_due_date->lte($today)) {
                        $charge = SubscriptionCharge::firstOrCreate(
                            ['subscription_id' => $subscription->id, 'period_start' => $subscription->next_due_date->toDateString()],
                            ['due_date' => $subscription->next_due_date, 'amount' => $subscription->amount, 'status' => 'por_cobrar'],
                        );
                        $created += (int) $charge->wasRecentlyCreated;
                        $subscription->next_due_date = $subscription->next_due_date->copy()->addMonthNoOverflow();
                    }
                    $subscription->save();
                });
            });

        return $created;
    }

    /** @param array<string, mixed> $data */
    public function recordPayment(SubscriptionCharge $charge, array $data): Payment
    {
        return DB::transaction(function () use ($charge, $data) {
            $subscription = $charge->subscription;

            $payment = Payment::create($data + [
                'client_id' => $subscription->client_id,
                'subscription_charge_id' => $charge->id,
                'currency' => $subscription->currency,
                'recorded_by' => auth()->id(),
            ]);

            if ($charge->amountPaid() >= (float) $charge->amount) {
                $charge->update(['status' => 'cobrado']);
            }

            Audit::record('suscripcion.cobro_registrado', $charge, ['monto' => $payment->amount]);

            return $payment;
        });
    }
}
