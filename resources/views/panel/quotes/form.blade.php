@php use App\Support\Permissions as P; $items = old('items', $items); @endphp
<x-layouts.panel :title="$quote->exists ? 'Editar '.$quote->number : 'Nueva cotización'">
    <form method="POST" action="{{ $quote->exists ? route('panel.quotes.update', $quote) : route('panel.quotes.store') }}" class="space-y-6">
        @csrf @if($quote->exists) @method('PUT') @endif
        <input type="hidden" name="lead_id" value="{{ old('lead_id', $quote->lead_id) }}">

        <section class="card grid gap-5 sm:grid-cols-2">
            <div>
                <label class="label" for="client_id">Cliente *</label>
                <select id="client_id" name="client_id" required class="input">
                    <option value="">Selecciona…</option>
                    @foreach ($clients as $c)<option value="{{ $c->id }}" @selected((int) old('client_id', $quote->client_id) === $c->id)>{{ $c->displayName() }}</option>@endforeach
                </select>
            </div>
            <x-field label="Título *" name="title" :value="$quote->title" required />
            <x-field label="Alcance" name="scope" type="textarea" rows="5" :value="$quote->scope" class="sm:col-span-2" />
            <div class="grid gap-5 sm:col-span-2 sm:grid-cols-4">
                <div><label class="label" for="currency">Moneda</label><select id="currency" name="currency" class="input">@foreach (['PEN', 'USD'] as $cur)<option @selected(old('currency', $quote->currency) === $cur)>{{ $cur }}</option>@endforeach</select></div>
                <x-field label="Impuesto (%)" name="tax_rate" type="number" step="0.01" min="0" :value="$quote->tax_rate" help="0 si no aplica; 18 para IGV." />
                <x-field label="Plazo de entrega (días)" name="delivery_days" type="number" min="1" :value="$quote->delivery_days" />
                <x-field label="Vigente hasta" name="valid_until" type="date" :value="$quote->valid_until?->toDateString()" />
            </div>
        </section>

        <section class="card" data-repeater>
            <div class="flex items-center justify-between"><h2 class="font-semibold">Partidas</h2><button type="button" data-repeater-add class="btn-secondary btn-sm">+ Partida</button></div>
            <div class="mt-4 space-y-3" data-repeater-list>
                @foreach ($items as $i => $item)
                    @include('panel.quotes.item-row', ['i' => $i, 'item' => $item])
                @endforeach
            </div>
            <template>@include('panel.quotes.item-row', ['i' => '__INDEX__', 'item' => ['description' => '', 'quantity' => 1, 'unit_price' => '']])</template>
            <p class="mt-4 text-right text-sm">Subtotal estimado: <strong data-repeater-total class="tabular-nums">0.00</strong></p>
        </section>

        <section class="card grid gap-5 sm:grid-cols-2">
            <x-field label="Condiciones" name="conditions" type="textarea" rows="5" :value="$quote->conditions" />
            @can(P::FINANCE_VIEW_MARGINS)
                <x-field label="Costo estimado interno (no se muestra al cliente)" name="estimated_cost" type="number" step="0.01" min="0" :value="$quote->estimated_cost" />
            @endcan
        </section>

        <button class="btn-primary">Guardar cotización</button>
    </form>
</x-layouts.panel>
