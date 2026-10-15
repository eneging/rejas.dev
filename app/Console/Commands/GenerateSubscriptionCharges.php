<?php

namespace App\Console\Commands;

use App\Services\SubscriptionBilling;
use Illuminate\Console\Command;

class GenerateSubscriptionCharges extends Command
{
    protected $signature = 'rejas:generar-cargos';

    protected $description = 'Genera los cargos "por cobrar" de las suscripciones activas vencidas';

    public function handle(SubscriptionBilling $billing): int
    {
        $this->info('Cargos generados: '.$billing->generateDueCharges());

        return self::SUCCESS;
    }
}
