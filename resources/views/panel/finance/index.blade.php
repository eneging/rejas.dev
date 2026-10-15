@php use App\Support\Money; @endphp
<x-layouts.panel title="Finanzas">
    <form class="flex flex-wrap items-end gap-3">
        <div><label class="label" for="desde">Desde</label><input id="desde" type="date" name="desde" value="{{ $from->toDateString() }}" class="input"></div>
        <div><label class="label" for="hasta">Hasta</label><input id="hasta" type="date" name="hasta" value="{{ $to->toDateString() }}" class="input"></div>
        <button class="btn-secondary">Aplicar</button>
    </form>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="card"><p class="muted">Dinero recibido en el periodo</p><p class="mt-1 font-display text-2xl font-semibold">{{ Money::format($payments->sum('amount')) }}</p></div>
        <div class="card"><p class="muted">Cuentas por cobrar (proyectos)</p><p class="mt-1 font-display text-2xl font-semibold">{{ Money::format($receivables->sum(fn ($p) => $p->balance())) }}</p></div>
        <div class="card"><p class="muted">Suscripciones por cobrar</p><p class="mt-1 font-display text-2xl font-semibold">{{ Money::format($pendingCharges->sum('amount')) }}</p></div>
    </div>

    <section class="card overflow-x-auto p-0">
        <h2 class="px-5 pt-5 font-semibold">Flujo de caja por mes</h2>
        <table class="table mt-3">
            <thead><tr><th>Mes</th><th class="text-right">Ingresos recibidos</th>@if($showMargins)<th class="text-right">Gastos</th><th class="text-right">Neto</th>@endif</tr></thead>
            <tbody>
            @foreach ($months as $m)
                <tr><td>{{ ucfirst($m['label']) }}</td><td class="text-right tabular-nums">{{ Money::format($m['income']) }}</td>@if($showMargins)<td class="text-right tabular-nums">{{ Money::format($m['expense']) }}</td><td class="text-right font-medium tabular-nums">{{ Money::format($m['income'] - $m['expense']) }}</td>@endif</tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="card overflow-x-auto p-0">
            <h2 class="px-5 pt-5 font-semibold">Cuentas por cobrar</h2>
            <table class="table mt-3">
                <thead><tr><th>Proyecto</th><th>Estado</th><th class="text-right">Saldo</th></tr></thead>
                <tbody>
                @forelse ($receivables as $p)
                    <tr><td><a class="hover:text-electric" href="{{ route('panel.projects.show', $p) }}">{{ $p->name }}</a><p class="text-xs text-slate-500">{{ $p->client->displayName() }}</p></td><td><x-payment-status :project="$p" /></td><td class="text-right tabular-nums">{{ Money::format($p->balance(), $p->currency) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-center text-slate-500">Nada por cobrar.</td></tr>
                @endforelse
                </tbody>
            </table>
        </section>
        <section class="card overflow-x-auto p-0">
            <h2 class="px-5 pt-5 font-semibold">Suscripciones por cobrar</h2>
            <table class="table mt-3">
                <thead><tr><th>Cliente / servicio</th><th>Vence</th><th class="text-right">Monto</th></tr></thead>
                <tbody>
                @forelse ($pendingCharges as $c)
                    <tr><td><a class="hover:text-electric" href="{{ route('panel.subscriptions.show', $c->subscription) }}">{{ $c->subscription->client->displayName() }}</a><p class="text-xs text-slate-500">{{ $c->subscription->service }}</p></td><td @class(['text-red-600' => $c->isOverdue()])>{{ $c->due_date->format('d/m/Y') }}</td><td class="text-right tabular-nums">{{ Money::format($c->amount, $c->subscription->currency) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-center text-slate-500">Sin cargos pendientes.</td></tr>
                @endforelse
                </tbody>
            </table>
        </section>
    </div>

    <section class="card overflow-x-auto p-0">
        <h2 class="px-5 pt-5 font-semibold">Pagos recibidos</h2>
        <table class="table mt-3">
            <thead><tr><th>Fecha</th><th>Cliente</th><th>Concepto</th><th>Método</th><th class="text-right">Importe</th></tr></thead>
            <tbody>
            @forelse ($payments as $pay)
                <tr><td>{{ $pay->paid_at->format('d/m/Y') }}</td><td>{{ $pay->client->displayName() }}</td><td>{{ $pay->project?->name ?? ($pay->subscription_charge_id ? 'Suscripción' : '—') }}</td><td>{{ $pay->method }} {{ $pay->reference }}</td><td class="text-right tabular-nums">{{ Money::format($pay->amount, $pay->currency) }}</td></tr>
            @empty
                <tr><td colspan="5" class="text-center text-slate-500">Sin pagos en el periodo.</td></tr>
            @endforelse
            </tbody>
        </table>
    </section>

    @if ($showMargins)
        <div class="grid gap-6 lg:grid-cols-2">
            <section class="card overflow-x-auto p-0">
                <h2 class="px-5 pt-5 font-semibold">Rentabilidad estimada (restringido)</h2>
                <table class="table mt-3">
                    <thead><tr><th>Proyecto</th><th class="text-right">Cotizado</th><th class="text-right">Utilidad est.</th></tr></thead>
                    <tbody>
                    @forelse ($margins as $p)
                        <tr><td>{{ $p->name }}</td><td class="text-right tabular-nums">{{ Money::format($p->contract_amount, $p->currency) }}</td><td class="text-right tabular-nums">{{ Money::format($p->estimatedMargin(), $p->currency) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-slate-500">Registra costos estimados en los proyectos para ver márgenes.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </section>
            <section class="card space-y-4">
                <h2 class="font-semibold">Registrar gasto</h2>
                <form method="POST" action="{{ route('panel.finance.expenses.store') }}" class="grid gap-3 sm:grid-cols-2">
                    @csrf
                    <input name="description" placeholder="Descripción" required class="input sm:col-span-2">
                    <input name="amount" type="number" step="0.01" min="0.01" placeholder="Importe" required class="input">
                    <input name="spent_at" type="date" value="{{ now()->toDateString() }}" required class="input">
                    <select name="currency" class="input"><option>PEN</option><option>USD</option></select>
                    <select name="financial_category_id" class="input"><option value="">Categoría</option>@foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
                    <select name="project_id" class="input sm:col-span-2"><option value="">Gasto general (sin proyecto)</option>@foreach ($projects as $p)<option value="{{ $p->id }}">{{ $p->code }} · {{ $p->name }}</option>@endforeach</select>
                    <button class="btn-primary sm:col-span-2 sm:justify-self-start">Registrar gasto</button>
                </form>
                <ul class="divide-y divide-slate-100 text-sm dark:divide-slate-800">
                    @foreach ($expenses->take(10) as $e)
                        <li class="flex justify-between py-2"><span>{{ $e->spent_at->format('d/m') }} · {{ $e->description }} <span class="text-xs text-slate-500">{{ $e->category?->name }}</span></span><span class="tabular-nums">{{ Money::format($e->amount, $e->currency) }}</span></li>
                    @endforeach
                </ul>
            </section>
        </div>
    @endif
</x-layouts.panel>
