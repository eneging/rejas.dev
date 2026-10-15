@php use App\Models\Lead; @endphp
<x-layouts.panel :title="'Prospecto · '.$lead->name">
    <x-slot:actions>
        <form method="POST" action="{{ route('panel.leads.convert', $lead) }}">@csrf
            <button class="btn-primary">{{ $lead->client_id ? 'Crear cotización' : 'Convertir en cliente y cotizar' }}</button>
        </form>
    </x-slot:actions>

    <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
        <section class="card space-y-4">
            <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                <div><dt class="label">Correo</dt><dd>{{ $lead->email }}</dd></div>
                <div><dt class="label">Teléfono</dt><dd>{{ $lead->phone ?? '—' }}</dd></div>
                <div><dt class="label">Empresa</dt><dd>{{ $lead->company ?? '—' }}</dd></div>
                <div><dt class="label">Servicio</dt><dd>{{ config('rejas.service_types')[$lead->service_type] ?? $lead->service_type }}@if($lead->plan_interest) · Plan {{ config('rejas.workshop.plans.'.$lead->plan_interest.'.name', $lead->plan_interest) }}@endif</dd></div>
                <div><dt class="label">Origen</dt><dd>{{ $lead->source }} · {{ $lead->created_at->format('d/m/Y H:i') }}</dd></div>
                <div><dt class="label">Cliente</dt><dd>@if($lead->client)<a class="link" href="{{ route('panel.clients.show', $lead->client) }}">{{ $lead->client->displayName() }}</a>@else — @endif</dd></div>
            </dl>
            <div><p class="label">Descripción</p><p class="text-sm whitespace-pre-line">{{ $lead->description }}</p></div>
            <div>
                <p class="label">Evidencia de consentimiento</p>
                <ul class="space-y-1 text-xs text-slate-600 dark:text-slate-400">
                    @forelse ($lead->consents as $consent)
                        <li>{{ ['privacy_policy' => 'Política de privacidad', 'marketing' => 'Comunicaciones comerciales', 'terms' => 'Términos'][$consent->type] ?? $consent->type }}:
                            <strong>{{ $consent->accepted ? 'Aceptado' : 'No aceptado' }}</strong> · v{{ $consent->document_version }} · {{ $consent->accepted_at->format('d/m/Y H:i') }} · IP {{ $consent->ip_address }}</li>
                    @empty
                        <li>Sin registros (prospecto creado manualmente).</li>
                    @endforelse
                </ul>
            </div>
        </section>

        <form method="POST" action="{{ route('panel.leads.update', $lead) }}" class="card space-y-4">
            @csrf @method('PATCH')
            <div><label class="label" for="status">Estado</label><select id="status" name="status" class="input">@foreach (Lead::STATUSES as $k => $v)<option value="{{ $k }}" @selected($lead->status === $k)>{{ $v }}</option>@endforeach</select></div>
            <div><label class="label" for="assigned_to">Responsable</label><select id="assigned_to" name="assigned_to" class="input"><option value="">Sin asignar</option>@foreach ($users as $u)<option value="{{ $u->id }}" @selected($lead->assigned_to === $u->id)>{{ $u->name }}</option>@endforeach</select></div>
            <x-field label="Notas internas" name="internal_notes" type="textarea" :value="$lead->internal_notes" />
            <button class="btn-primary">Guardar</button>
        </form>
    </div>
</x-layouts.panel>
