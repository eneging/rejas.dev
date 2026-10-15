<?php

namespace App\Support;

/** Precios publicados del sistema para talleres (config/rejas.php → workshop). */
final class Pricing
{
    /** @return array<string, array<string, mixed>> */
    public static function workshopPlans(): array
    {
        return collect(config('rejas.workshop.plans'))
            ->map(fn (array $plan) => $plan + ['annual' => self::annual($plan['monthly'])])
            ->all();
    }

    /** Pago anual adelantado: se cobran N meses (10) por 12. */
    public static function annual(float|int $monthly): float
    {
        return $monthly * (int) config('rejas.workshop.annual_months_charged', 10);
    }

    public static function annualSaving(float|int $monthly): float
    {
        return $monthly * 12 - self::annual($monthly);
    }

    /** Leyenda de impuestos según config('rejas.tax_display'); null si no debe mostrarse. */
    public static function taxNote(): ?string
    {
        return match (config('rejas.tax_display')) {
            'incluido' => 'Incluye IGV',
            'adicional' => '+ IGV',
            default => null,
        };
    }

    public static function amount(float|int $value): string
    {
        return 'S/'.number_format($value, $value == (int) $value ? 0 : 2, '.', ',');
    }
}
