@php use App\Support\Permissions as P; @endphp
<x-layouts.panel title="Nuevo proyecto">
    <form method="POST" action="{{ route('panel.projects.store') }}" class="card grid max-w-4xl gap-5 sm:grid-cols-2">
        @csrf
        <x-field label="Nombre *" name="name" required />
        <div>
            <label class="label" for="client_id">Cliente *</label>
            <select id="client_id" name="client_id" required class="input"><option value="">Selecciona…</option>@foreach ($clients as $c)<option value="{{ $c->id }}" @selected(old('client_id') == $c->id)>{{ $c->displayName() }}</option>@endforeach</select>
        </div>
        <div class="sm:col-span-2">
            <label class="label" for="project_template_id">Plantilla de servicio</label>
            <select id="project_template_id" name="project_template_id" class="input"><option value="">Sin plantilla</option>@foreach ($templates as $t)<option value="{{ $t->id }}" @selected(old('project_template_id') == $t->id)>{{ $t->name }} ({{ $t->tasks_count }} tareas)</option>@endforeach</select>
            <p class="mt-1 text-xs text-slate-500">Las tareas y estimaciones se copian al proyecto; cambios futuros a la plantilla no lo afectan.</p>
        </div>
        <x-field label="Descripción" name="description" type="textarea" class="sm:col-span-2" />
        <x-field label="Inicio *" name="start_date" type="date" :value="now()->toDateString()" required />
        <x-field label="Entrega contractual *" name="contract_due_date" type="date" required />
        <x-field label="Margen de planificación (%)" name="planning_margin_pct" type="number" min="0" max="60" value="25" help="Reserva interna sobre el plazo contractual (20–30 % sugerido)." />
        <x-field label="Entrega interna" name="internal_due_date" type="date" help="Si la dejas vacía se calcula con el margen." />
        <div><label class="label" for="currency">Moneda</label><select id="currency" name="currency" class="input"><option>PEN</option><option>USD</option></select></div>
        <x-field label="Monto del proyecto" name="contract_amount" type="number" step="0.01" min="0" help="Genera el plan de pagos 50 % / 50 % por defecto." />
        @can(P::FINANCE_VIEW_MARGINS)<x-field label="Costo estimado (interno)" name="estimated_cost" type="number" step="0.01" min="0" />@endcan
        <fieldset class="sm:col-span-2">
            <legend class="label">Equipo</legend>
            <div class="flex flex-wrap gap-4">@foreach ($users as $u)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="members[]" value="{{ $u->id }}" class="accent-electric" @checked(in_array($u->id, old('members', [])))> {{ $u->name }}</label>@endforeach</div>
        </fieldset>
        <div class="sm:col-span-2"><button class="btn-primary">Crear proyecto</button></div>
    </form>
</x-layouts.panel>
