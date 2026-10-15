@php use App\Models\Lead; @endphp
<x-layouts.panel title="Prospectos">
    <form class="flex flex-wrap gap-3">
        <input name="q" value="{{ request('q') }}" placeholder="Buscar nombre, correo o empresa" class="input max-w-xs">
        <select name="estado" class="input w-auto"><option value="">Todos los estados</option>@foreach (Lead::STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('estado') === $k)>{{ $v }}</option>@endforeach</select>
        <button class="btn-secondary">Filtrar</button>
    </form>
    <div class="card overflow-x-auto p-0">
        <table class="table">
            <thead><tr><th>Fecha</th><th>Nombre</th><th>Servicio</th><th>Contacto</th><th>Responsable</th><th>Estado</th></tr></thead>
            <tbody>
            @forelse ($leads as $lead)
                <tr>
                    <td class="whitespace-nowrap">{{ $lead->created_at->format('d/m/Y') }}</td>
                    <td><a href="{{ route('panel.leads.show', $lead) }}" class="font-medium hover:text-electric">{{ $lead->name }}</a>@if($lead->company)<p class="text-xs text-slate-500">{{ $lead->company }}</p>@endif</td>
                    <td>{{ config('rejas.service_types')[$lead->service_type] ?? $lead->service_type }}@if($lead->plan_interest)<p class="text-xs text-slate-500">Plan {{ config('rejas.workshop.plans.'.$lead->plan_interest.'.name', $lead->plan_interest) }}</p>@endif</td>
                    <td class="text-xs">{{ $lead->email }}<br>{{ $lead->phone }}</td>
                    <td>{{ $lead->assignee?->name ?? '—' }}</td>
                    <td><x-status-badge :status="$lead->status" :label="$lead->statusLabel()" /></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-slate-500">Sin prospectos.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $leads->links() }}
</x-layouts.panel>
