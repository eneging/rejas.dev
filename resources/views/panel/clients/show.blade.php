@php use App\Support\Money; use App\Support\Permissions as P; @endphp
<x-layouts.panel :title="$client->displayName()">
    <x-slot:actions>
        <a href="{{ route('panel.clients.edit', $client) }}" class="btn-secondary">Editar</a>
        @can(P::QUOTES_MANAGE)<a href="{{ route('panel.quotes.create', ['client_id' => $client->id]) }}" class="btn-primary">Nueva cotización</a>@endcan
    </x-slot:actions>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="card space-y-3 text-sm">
            <p><span class="label">Contacto</span>{{ $client->name }}</p>
            <p><span class="label">Documento</span>{{ $client->document_type }} {{ $client->document_number ?: '—' }}</p>
            <p><span class="label">Correo / teléfono</span>{{ $client->email ?: '—' }} · {{ $client->phone ?: '—' }}</p>
            <p><span class="label">Sector</span>{{ $client->sector ?: '—' }}</p>
            @if($client->notes)<p class="whitespace-pre-line"><span class="label">Notas</span>{{ $client->notes }}</p>@endif
        </section>

        <section class="card lg:col-span-2">
            <h2 class="font-semibold">Contactos</h2>
            <ul class="mt-3 divide-y divide-slate-100 text-sm dark:divide-slate-800">
                @forelse ($client->contacts as $c)
                    <li class="py-2"><strong>{{ $c->name }}</strong> {{ $c->position ? '· '.$c->position : '' }} — {{ $c->email }} {{ $c->phone }}</li>
                @empty
                    <li class="py-2 text-slate-500">Sin contactos adicionales.</li>
                @endforelse
            </ul>
            <form method="POST" action="{{ route('panel.clients.contacts.store', $client) }}" class="mt-4 grid gap-3 sm:grid-cols-5">
                @csrf
                <input name="name" placeholder="Nombre" required class="input">
                <input name="position" placeholder="Cargo" class="input">
                <input name="email" placeholder="Correo" class="input">
                <input name="phone" placeholder="Teléfono" class="input">
                <button class="btn-secondary">Agregar</button>
            </form>
        </section>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="card">
            <h2 class="font-semibold">Cotizaciones</h2>
            <ul class="mt-3 divide-y divide-slate-100 text-sm dark:divide-slate-800">
                @forelse ($client->quotes as $q)
                    <li class="flex justify-between py-2"><a class="hover:text-electric" href="{{ route('panel.quotes.show', $q) }}">{{ $q->number }} · {{ $q->title }}</a><x-status-badge :status="$q->status" :label="$q->statusLabel()" /></li>
                @empty <li class="py-2 text-slate-500">Sin cotizaciones.</li> @endforelse
            </ul>
        </section>
        <section class="card">
            <h2 class="font-semibold">Proyectos</h2>
            <ul class="mt-3 divide-y divide-slate-100 text-sm dark:divide-slate-800">
                @forelse ($client->projects as $p)
                    <li class="flex justify-between py-2"><a class="hover:text-electric" href="{{ route('panel.projects.show', $p) }}">{{ $p->code }} · {{ $p->name }}</a><x-status-badge :status="$p->status" :label="$p->statusLabel()" /></li>
                @empty <li class="py-2 text-slate-500">Sin proyectos.</li> @endforelse
            </ul>
        </section>
        @can(P::FINANCE_VIEW)
        <section class="card lg:col-span-2">
            <h2 class="font-semibold">Pagos recibidos</h2>
            <ul class="mt-3 divide-y divide-slate-100 text-sm dark:divide-slate-800">
                @forelse ($client->payments as $pay)
                    <li class="flex justify-between py-2"><span>{{ $pay->paid_at->format('d/m/Y') }} · {{ $pay->method }} {{ $pay->reference }}</span><span class="tabular-nums">{{ Money::format($pay->amount, $pay->currency) }}</span></li>
                @empty <li class="py-2 text-slate-500">Sin pagos registrados.</li> @endforelse
            </ul>
        </section>
        @endcan
    </div>
</x-layouts.panel>
