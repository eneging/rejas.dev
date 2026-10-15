<?php

namespace App\Support;

final class Money
{
    public static function format(float|string|null $amount, string $currency = 'PEN'): string
    {
        $symbol = match ($currency) {
            'PEN' => 'S/',
            'USD' => 'US$',
            'EUR' => '€',
            default => $currency,
        };

        return $symbol.' '.number_format((float) $amount, 2, '.', ',');
    }
}
