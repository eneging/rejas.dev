<x-layouts.panel title="Nueva suscripción">
    <form method="POST" action="{{ route('panel.subscriptions.store') }}" class="card grid max-w-3xl gap-5 sm:grid-cols-2">
        @csrf
        <div><label class="label" for="client_id">Cliente *</label><select id="client_id" name="client_id" required class="input"><option value="">Selecciona…</option>@foreach ($clients as $c)<option value="{{ $c->id }}" @selected(old('client_id') == $c->id)>{{ $c->displayName() }}</option>@endforeach</select></div>
        <div><label class="label" for="subscription_plan_id">Plan</label><select id="subscription_plan_id" name="subscription_plan_id" class="input"><option value="">Personalizado</option>@foreach ($plans as $p)<option value="{{ $p->id }}">{{ $p->name }} ({{ $p->monthly_amount }})</option>@endforeach</select></div>
        <x-field label="Servicio *" name="service" required placeholder="Ej.: Hosting y soporte del sistema de inventario" class="sm:col-span-2" />
        <x-field label="Monto mensual *" name="amount" type="number" step="0.01" min="0.01" required />
        <div><label class="label" for="currency">Moneda</label><select id="currency" name="currency" class="input"><option>PEN</option><option>USD</option></select></div>
        <x-field label="Inicio / primer vencimiento *" name="starts_at" type="date" :value="now()->toDateString()" required />
        <x-field label="Notas" name="notes" type="textarea" class="sm:col-span-2" />
        <div class="sm:col-span-2"><button class="btn-primary">Registrar suscripción</button></div>
    </form>
</x-layouts.panel>
