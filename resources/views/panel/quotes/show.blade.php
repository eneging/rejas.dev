@php use App\Support\Money; use App\Support\Permissions as P; @endphp
<x-layouts.panel :title="$quote->number.' · '.$quote->title">
    <x-slot:actions>
        <a href="{{ route('panel.quotes.print', $quote) }}" target="_blank" class="btn-secondary">Ver / imprimir PDF</a>
        @if ($quote->isEditable())<a href="{{ route('panel.quotes.edit', $quote) }}" class="btn-secondary">Editar</a>@endif
    </x-slot:actions>

    <div class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
        <section class="card space-y-5">
            <div class="flex flex-wrap items-center gap-3">
                <x-status-badge :status="$quote->isExpired() ? 'vencida' : $quote->status" :label="$quote->statusLabel()" />
                <span class="muted">Versión {{ $quote->version }} · Cliente: <a class="link" href="{{ route('panel.clients.show', $quote->client) }}">{{ $quote->client->displayName() }}</a></span>
            </div>
            @if ($quote->scope)<div><p class="label">Alcance</p><p class="text-sm whitespace-pre-line">{{ $quote->scope }}</p></div>@endif
            <table class="table">
                <thead><tr><th>Descripción</th><th class="text-right">Cant.</th><th class="text-right">P. unit.</th><th class="text-right">Total</th></tr></thead>
                <tbody>
                @foreach ($quote->items as $item)
                    <tr><td>{{ $item->description }}</td><td class="text-right">{{ (float) $item->quantity }}</td><td class="text-right tabular-nums">{{ Money::format($item->unit_price, $quote->currency) }}</td><td class="text-right tabular-nums">{{ Money::format($item->total, $quote->currency) }}</td></tr>
                @endforeach
                </tbody>
                <tfoot class="text-sm">
                    <tr><td colspan="3" class="px-3 pt-3 text-right">Subtotal</td><td class="px-3 pt-3 text-right tabular-nums">{{ Money::format($quote->subtotal, $quote->currency) }}</td></tr>
                    @if ((float) $quote->tax_rate > 0)<tr><td colspan="3" class="px-3 text-right">Impuesto ({{ (float) $quote->tax_rate }} %)</td><td class="px-3 text-right tabular-nums">{{ Money::format($quote->tax_amount, $quote->currency) }}</td></tr>@endif
                    <tr><td colspan="3" class="px-3 text-right font-semibold">Total</td><td class="px-3 text-right font-semibold tabular-nums">{{ Money::format($quote->total, $quote->currency) }}</td></tr>
                </tfoot>
            </table>
            @can(P::FINANCE_VIEW_MARGINS)
                @if ($quote->estimated_cost !== null)
                    <p class="rounded-lg bg-slate-50 p-3 text-sm dark:bg-slate-800">Costo estimado: {{ Money::format($quote->estimated_cost, $quote->currency) }} · Margen estimado: <strong>{{ Money::format($quote->subtotal - $quote->estimated_cost, $quote->currency) }}</strong></p>
                @endif
            @endcan
        </section>

        <div class="space-y-6">
            @can(P::QUOTES_SEND)
                @if ($quote->isEditable())
                    <form method="POST" action="{{ route('panel.quotes.send', $quote) }}" class="card space-y-3">
                        @csrf
                        <h2 class="font-semibold">{{ $quote->sent_at ? 'Reenviar (nueva versión)' : 'Registrar envío' }}</h2>
                        <select name="channel" class="input">@foreach (['correo' => 'Correo', 'whatsapp' => 'WhatsApp', 'presencial' => 'Presencial', 'otro' => 'Otro'] as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
                        <button class="btn-primary w-full">Marcar como enviada</button>
                    </form>
                @endif

                @if ($quote->status === 'enviada')
                    <form method="POST" action="{{ route('panel.quotes.accept', $quote) }}" class="card space-y-3">
                        @csrf
                        <h2 class="font-semibold">Registrar aceptación</h2>
                        <p class="text-xs text-slate-500">Abrir el PDF no es aceptación. Registra la evidencia concreta (correo, mensaje, firma).</p>
                        <select name="channel" class="input">@foreach (['correo' => 'Correo', 'whatsapp' => 'WhatsApp', 'firma' => 'Firma', 'presencial' => 'Presencial', 'otro' => 'Otro'] as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
                        <textarea name="evidence" rows="3" class="input" placeholder="Ej.: correo del 15/10 de juan@cliente.pe: «Aceptamos la propuesta v2»" required></textarea>
                        <label class="flex items-center gap-2 text-sm"><input type="hidden" name="create_project" value="0"><input type="checkbox" name="create_project" value="1" checked class="accent-electric"> Crear proyecto automáticamente</label>
                        <button class="btn-primary w-full">Aceptar cotización</button>
                    </form>
                    <form method="POST" action="{{ route('panel.quotes.reject', $quote) }}" data-confirm="¿Marcar la cotización como rechazada?">@csrf<button class="btn-danger w-full">Marcar como rechazada</button></form>
                @endif
            @endcan

            @if ($quote->status === 'aceptada')
                <section class="card space-y-2 text-sm">
                    <h2 class="font-semibold">Aceptación</h2>
                    <p>{{ $quote->accepted_at->format('d/m/Y H:i') }} vía {{ $quote->acceptance_channel }}</p>
                    <p class="whitespace-pre-line text-slate-600 dark:text-slate-400">{{ $quote->acceptance_evidence }}</p>
                    @if ($quote->contract)<p>Contrato: <strong>{{ $quote->contract->number }}</strong> ({{ $quote->contract->status }})</p>@endif
                    @if ($quote->project)<p>Proyecto: <a class="link" href="{{ route('panel.projects.show', $quote->project) }}">{{ $quote->project->code }}</a></p>@endif
                </section>
            @endif

            <section class="card">
                <h2 class="font-semibold">Historial de versiones</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($quote->versions as $v)
                        <li>v{{ $v->version }} · {{ $v->created_at->format('d/m/Y H:i') }} · {{ $v->reason }} · {{ Money::format($v->snapshot['quote']['total'] ?? 0, $quote->currency) }} <span class="text-slate-500">({{ $v->author?->name }})</span></li>
                    @empty
                        <li class="text-slate-500">Aún no se ha enviado.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
</x-layouts.panel>
