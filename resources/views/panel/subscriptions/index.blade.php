@php use App\Models\Subscription; use App\Support\Money; @endphp
<x-layouts.panel title="Suscripciones">
    <x-slot:actions>
        <form method="POST" action="{{ route('panel.subscriptions.generate') }}">@csrf<button class="btn-secondary">Generar cargos vencidos</button></form>
        <a href="{{ route('panel.subscriptions.create') }}" class="btn-primary">Nueva suscripción</a>
    </x-slot:actions>
    <p class="muted">Registro administrativo: los cobros se acuerdan con cada cliente fuera de la web. Un periodo vencido (mensual o anual) queda <strong>por cobrar</strong> hasta registrar el pago real.</p>
    <form class="flex gap-3"><select name="estado" class="input w-auto"><option value="">Todas</option>@foreach (Subscription::STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('estado') === $k)>{{ $v }}</option>@endforeach</select><button class="btn-secondary">Filtrar</button></form>
    <div class="card overflow-x-auto p-0">
        <table class="table">
            <thead><tr><th>Cliente</th><th>Servicio</th><th class="text-right">Monto</th><th>Siguiente vencimiento</th><th>Por cobrar</th><th>Estado</th></tr></thead>
            <tbody>
            @forelse ($subscriptions as $s)
                <tr>
                    <td><a href="{{ route('panel.subscriptions.show', $s) }}" class="font-medium hover:text-electric">{{ $s->client->displayName() }}</a></td>
                    <td>{{ $s->service }}</td>
                    <td class="text-right tabular-nums">{{ Money::format($s->amount, $s->currency) }}<p class="text-xs text-slate-500">{{ $s->periodicity === 'anual' ? 'por año' : 'por mes' }}</p></td>
                    <td>{{ $s->next_due_date->format('d/m/Y') }}</td>
                    <td>@if ($s->charges->isNotEmpty())<span class="badge bg-amber-50 text-amber-700">{{ $s->charges->count() }} · {{ Money::format($s->charges->sum('amount'), $s->currency) }}</span>@else <span class="text-xs text-slate-400">Al día</span> @endif</td>
                    <td><x-status-badge :status="$s->status" :label="$s->statusLabel()" /></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-slate-500">Sin suscripciones.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $subscriptions->links() }}
</x-layouts.panel>
