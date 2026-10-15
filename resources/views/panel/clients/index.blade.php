<x-layouts.panel title="Clientes">
    <x-slot:actions><a href="{{ route('panel.clients.create') }}" class="btn-primary">Nuevo cliente</a></x-slot:actions>
    <form class="flex gap-3"><input name="q" value="{{ request('q') }}" placeholder="Buscar por nombre, razón social o documento" class="input max-w-sm"><button class="btn-secondary">Buscar</button></form>
    <div class="card overflow-x-auto p-0">
        <table class="table">
            <thead><tr><th>Cliente</th><th>Documento</th><th>Contacto</th><th>Sector</th><th class="text-right">Proyectos</th><th class="text-right">Suscripciones</th></tr></thead>
            <tbody>
            @forelse ($clients as $client)
                <tr>
                    <td><a href="{{ route('panel.clients.show', $client) }}" class="font-medium hover:text-electric">{{ $client->displayName() }}</a>@if($client->business_name)<p class="text-xs text-slate-500">{{ $client->name }}</p>@endif</td>
                    <td>{{ $client->document_type }} {{ $client->document_number }}</td>
                    <td class="text-xs">{{ $client->email }}<br>{{ $client->phone }}</td>
                    <td>{{ $client->sector ?? '—' }}</td>
                    <td class="text-right tabular-nums">{{ $client->projects_count }}</td>
                    <td class="text-right tabular-nums">{{ $client->subscriptions_count }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-slate-500">Aún no hay clientes.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $clients->links() }}
</x-layouts.panel>
