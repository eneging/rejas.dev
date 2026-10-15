<x-layouts.panel :title="$client->exists ? 'Editar cliente' : 'Nuevo cliente'">
    <form method="POST" action="{{ $client->exists ? route('panel.clients.update', $client) : route('panel.clients.store') }}" class="card grid max-w-3xl gap-5 sm:grid-cols-2">
        @csrf @if($client->exists) @method('PUT') @endif
        <x-field label="Nombre de contacto *" name="name" :value="$client->name" required />
        <x-field label="Razón social" name="business_name" :value="$client->business_name" />
        <div>
            <label class="label" for="document_type">Tipo de documento</label>
            <select id="document_type" name="document_type" class="input"><option value="">—</option>@foreach (['DNI', 'RUC', 'CE'] as $t)<option @selected(old('document_type', $client->document_type) === $t)>{{ $t }}</option>@endforeach</select>
        </div>
        <x-field label="Número de documento" name="document_number" :value="$client->document_number" help="Solo cuando sea necesario para contratación o comprobantes." />
        <x-field label="Correo" name="email" type="email" :value="$client->email" />
        <x-field label="Teléfono" name="phone" :value="$client->phone" />
        <x-field label="Sector" name="sector" :value="$client->sector" />
        <x-field label="Dirección" name="address" :value="$client->address" />
        <x-field label="Notas" name="notes" type="textarea" :value="$client->notes" class="sm:col-span-2" />
        <div class="sm:col-span-2"><button class="btn-primary">Guardar</button></div>
    </form>
</x-layouts.panel>
