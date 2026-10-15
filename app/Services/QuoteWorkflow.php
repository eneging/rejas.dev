<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Project;
use App\Models\Quote;
use App\Support\Audit;
use App\Support\Numbering;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class QuoteWorkflow
{
    public function __construct(private ProjectBuilder $projects) {}

    /**
     * Registra el envío y guarda una versión inmutable de lo enviado.
     * Reenviar tras cambios genera una nueva versión.
     */
    public function send(Quote $quote, string $channel): Quote
    {
        return DB::transaction(function () use ($quote, $channel) {
            $quote->load('items');
            $lastVersion = (int) $quote->versions()->max('version');
            $version = $lastVersion + 1;

            $quote->forceFill([
                'status' => 'enviada',
                'sent_at' => now(),
                'sent_via' => $channel,
                'version' => $version,
            ])->save();

            $quote->versions()->create([
                'version' => $version,
                'snapshot' => $quote->snapshot(),
                'reason' => $lastVersion ? 'Reenvío con cambios' : 'Envío inicial',
                'created_by' => auth()->id(),
            ]);

            Audit::record('cotizacion.enviada', $quote, ['version' => $version, 'canal' => $channel]);

            return $quote;
        });
    }

    /**
     * Registra la aceptación con evidencia explícita del canal usado.
     * Abrir el PDF NO es aceptación: sin evidencia no se acepta.
     */
    public function accept(Quote $quote, string $channel, string $evidence, bool $createProject = true): ?Project
    {
        if (trim($evidence) === '') {
            throw new InvalidArgumentException('La aceptación requiere evidencia.');
        }
        if ($quote->status !== 'enviada') {
            throw new InvalidArgumentException('Solo se puede aceptar una cotización enviada.');
        }

        return DB::transaction(function () use ($quote, $channel, $evidence, $createProject) {
            $quote->forceFill([
                'status' => 'aceptada',
                'accepted_at' => now(),
                'acceptance_channel' => $channel,
                'acceptance_evidence' => $evidence,
            ])->save();

            Contract::create([
                'number' => Numbering::next(Contract::class, 'CTR'),
                'client_id' => $quote->client_id,
                'quote_id' => $quote->id,
                'status' => 'borrador',
            ]);
            $quote->load('contract');

            $quote->lead?->update(['status' => 'ganado']);

            Audit::record('cotizacion.aceptada', $quote, ['canal' => $channel]);

            return $createProject ? $this->projects->fromQuote($quote) : null;
        });
    }
}
