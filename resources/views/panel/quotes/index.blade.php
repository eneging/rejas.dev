@php use App\Models\Quote; use App\Support\Money; @endphp
<x-layouts.panel title="Cotizaciones">
    <x-slot:actions><a href="{{ route('panel.quotes.create') }}" class="btn-primary">Nueva cotización</a></x-slot:actions>
    <form class="flex gap-3">
        <select name="estado" class="input w-auto"><option value="">Todos los estados</option>@foreach (Quote::STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('estado') === $k)>{{ $v }}</option>@endforeach</select>
        <button class="btn-secondary">Filtrar</button>
    </form>
    <div class="card overflow-x-auto p-0">
        <table class="table">
            <thead><tr><th>Número</th><th>Cliente</th><th>Título</th><th class="text-right">Total</th><th>Versión</th><th>Vigencia</th><th>Estado</th></tr></thead>
            <tbody>
            @forelse ($quotes as $quote)
                <tr>
                    <td><a href="{{ route('panel.quotes.show', $quote) }}" class="font-medium hover:text-electric">{{ $quote->number }}</a></td>
                    <td>{{ $quote->client->displayName() }}</td>
                    <td>{{ $quote->title }}</td>
                    <td class="text-right tabular-nums">{{ Money::format($quote->total, $quote->currency) }}</td>
                    <td>v{{ $quote->version }}</td>
                    <td>{{ $quote->valid_until?->format('d/m/Y') ?? '—' }}</td>
                    <td><x-status-badge :status="$quote->isExpired() ? 'vencida' : $quote->status" :label="$quote->statusLabel()" /></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-slate-500">Sin cotizaciones.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $quotes->links() }}
</x-layouts.panel>
