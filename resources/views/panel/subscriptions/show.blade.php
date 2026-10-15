@php use App\Models\Subscription; use App\Support\Money; @endphp
<x-layouts.panel :title="$subscription->service">
    <div class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
        <section class="card overflow-x-auto p-0">
            <h2 class="px-5 pt-5 font-semibold">Cargos</h2>
            <table class="table mt-3">
                <thead><tr><th>Periodo</th><th>Vence</th><th class="text-right">Monto</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                @forelse ($subscription->charges as $charge)
                    <tr>
                        <td>{{ ucfirst($charge->period_start->translatedFormat('F Y')) }}</td>
                        <td @class(['text-red-600 font-medium' => $charge->isOverdue()])>{{ $charge->due_date->format('d/m/Y') }}</td>
                        <td class="text-right tabular-nums">{{ Money::format($charge->amount, $subscription->currency) }}</td>
                        <td><x-status-badge :status="$charge->status" :label="['por_cobrar' => 'Por cobrar', 'cobrado' => 'Cobrado', 'anulado' => 'Anulado'][$charge->status]" /></td>
                        <td>
                            @if ($charge->status === 'por_cobrar')
                                <details>
                                    <summary class="link cursor-pointer text-xs">Registrar cobro</summary>
                                    <form method="POST" action="{{ route('panel.subscriptions.pay', $charge) }}" class="mt-2 grid gap-2">
                                        @csrf
                                        <input name="amount" type="number" step="0.01" value="{{ $charge->amount - $charge->payments->sum('amount') }}" class="input" aria-label="Importe">
                                        <input name="paid_at" type="date" value="{{ now()->toDateString() }}" class="input" aria-label="Fecha">
                                        <select name="method" class="input" aria-label="Método">@foreach ($paymentMethods as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
                                        <input name="reference" placeholder="Referencia" class="input" aria-label="Referencia">
                                        <button class="btn-primary btn-sm">Confirmar cobro</button>
                                    </form>
                                </details>
                            @else
                                <span class="text-xs text-slate-500">{{ $charge->payments->first()?->paid_at?->format('d/m/Y') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-slate-500">Aún no hay cargos generados.</td></tr>
                @endforelse
                </tbody>
            </table>
        </section>

        <form method="POST" action="{{ route('panel.subscriptions.update', $subscription) }}" class="card space-y-4">
            @csrf @method('PUT')
            <p class="text-sm">Cliente: <a class="link" href="{{ route('panel.clients.show', $subscription->client) }}">{{ $subscription->client->displayName() }}</a> · desde {{ $subscription->starts_at->format('d/m/Y') }}</p>
            <div><label class="label" for="status">Estado</label><select id="status" name="status" class="input">@foreach (Subscription::STATUSES as $k => $v)<option value="{{ $k }}" @selected($subscription->status === $k)>{{ $v }}</option>@endforeach</select></div>
            <x-field label="Monto mensual" name="amount" type="number" step="0.01" :value="$subscription->amount" />
            <x-field label="Siguiente vencimiento" name="next_due_date" type="date" :value="$subscription->next_due_date->toDateString()" />
            <button class="btn-primary">Guardar</button>
        </form>
    </div>
</x-layouts.panel>
