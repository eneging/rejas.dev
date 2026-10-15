@php use App\Support\Money; @endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $quote->number }} · Rejas.dev</title>
    @vite(['resources/css/app.css'])
    <style>@page { size: A4; margin: 18mm; } @media print { .no-print { display: none } body { background: #fff } }</style>
</head>
<body class="bg-slate-100 font-sans text-slate-800">
    <div class="no-print mx-auto flex max-w-3xl justify-end gap-2 py-4"><button onclick="window.print()" class="btn-primary">Imprimir / Guardar como PDF</button></div>
    <article class="mx-auto max-w-3xl bg-white p-12 shadow-sm print:p-0 print:shadow-none">
        <header class="flex items-start justify-between border-b border-slate-200 pb-6">
            <div>
                <p class="font-display text-xl font-bold tracking-[0.18em] text-ink">REJAS<span class="text-electric">.DEV</span></p>
                <p class="mt-1 text-xs text-slate-500">{{ config('rejas.domain') }} · {{ config('rejas.contact_email') }}</p>
            </div>
            <div class="text-right text-sm">
                <p class="font-display text-lg font-semibold">Cotización {{ $quote->number }}</p>
                <p>Versión {{ $quote->version }} · {{ ($quote->sent_at ?? $quote->created_at)->format('d/m/Y') }}</p>
                @if ($quote->valid_until)<p>Válida hasta {{ $quote->valid_until->format('d/m/Y') }}</p>@endif
            </div>
        </header>

        <section class="mt-6 text-sm">
            <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Cliente</p>
            <p class="font-medium">{{ $quote->client->displayName() }}</p>
            @if ($quote->client->document_number)<p>{{ $quote->client->document_type }} {{ $quote->client->document_number }}</p>@endif
            <p>{{ $quote->client->email }}</p>
        </section>

        <h1 class="mt-8 text-2xl font-semibold">{{ $quote->title }}</h1>
        @if ($quote->scope)<div class="mt-3 text-sm whitespace-pre-line">{{ $quote->scope }}</div>@endif

        <table class="mt-8 w-full text-sm">
            <thead><tr class="border-b-2 border-ink text-left"><th class="py-2">Descripción</th><th class="py-2 text-right">Cant.</th><th class="py-2 text-right">P. unit.</th><th class="py-2 text-right">Total</th></tr></thead>
            <tbody>
            @foreach ($quote->items as $item)
                <tr class="border-b border-slate-200"><td class="py-2">{{ $item->description }}</td><td class="py-2 text-right">{{ (float) $item->quantity }}</td><td class="py-2 text-right">{{ Money::format($item->unit_price, $quote->currency) }}</td><td class="py-2 text-right">{{ Money::format($item->total, $quote->currency) }}</td></tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr><td colspan="3" class="pt-3 text-right">Subtotal</td><td class="pt-3 text-right">{{ Money::format($quote->subtotal, $quote->currency) }}</td></tr>
                @if ((float) $quote->tax_rate > 0)<tr><td colspan="3" class="text-right">Impuesto ({{ (float) $quote->tax_rate }} %)</td><td class="text-right">{{ Money::format($quote->tax_amount, $quote->currency) }}</td></tr>@endif
                <tr class="text-base font-semibold"><td colspan="3" class="pt-1 text-right">Total</td><td class="pt-1 text-right">{{ Money::format($quote->total, $quote->currency) }}</td></tr>
            </tfoot>
        </table>

        @if ($quote->delivery_days)<p class="mt-8 text-sm"><strong>Plazo de entrega:</strong> {{ $quote->delivery_days }} días calendario desde la aceptación y el pago del adelanto.</p>@endif
        @if ($quote->conditions)
            <section class="mt-6 text-sm"><p class="font-semibold">Condiciones</p><div class="mt-1 whitespace-pre-line">{{ $quote->conditions }}</div></section>
        @endif
        <footer class="mt-12 border-t border-slate-200 pt-4 text-xs text-slate-500">Esta cotización no constituye contrato. La aceptación debe confirmarse por escrito.</footer>
    </article>
</body>
</html>
