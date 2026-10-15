<x-layouts.panel title="Portafolio">
    <x-slot:actions><a href="{{ route('panel.portfolio.create') }}" class="btn-primary">Nuevo caso</a></x-slot:actions>
    <div class="card overflow-x-auto p-0">
        <table class="table">
            <thead><tr><th>Orden</th><th>Título</th><th>Sector</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>{{ $item->position }}</td>
                    <td class="font-medium">{{ $item->title }}<p class="text-xs text-slate-500">{{ $item->summary }}</p></td>
                    <td>{{ $item->sector }}</td>
                    <td>@if ($item->is_published)<x-status-badge status="activa" label="Publicado" />@else<x-status-badge status="borrador" label="Borrador" />@endif</td>
                    <td class="text-right whitespace-nowrap"><a class="link text-sm" href="{{ route('panel.portfolio.edit', $item) }}">Editar</a>@if($item->is_published) · <a class="link text-sm" target="_blank" href="{{ route('portfolio.show', $item) }}">Ver</a>@endif</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-slate-500">Aún no hay casos publicados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.panel>
