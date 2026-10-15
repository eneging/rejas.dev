<x-layouts.panel :title="$user->exists ? 'Editar usuario' : 'Nuevo usuario'">
    <form method="POST" action="{{ $user->exists ? route('panel.users.update', $user) : route('panel.users.store') }}" class="card grid max-w-2xl gap-5">
        @csrf @if($user->exists) @method('PUT') @endif
        <x-field label="Nombre *" name="name" :value="$user->name" required />
        <x-field label="Correo *" name="email" type="email" :value="$user->email" required />
        <x-field label="{{ $user->exists ? 'Nueva contraseña (opcional)' : 'Contraseña *' }}" name="password" type="password" autocomplete="new-password" help="Mínimo 10 caracteres con letras y números." />
        <fieldset>
            <legend class="label">Roles *</legend>
            <div class="flex flex-wrap gap-4">@foreach ($roles as $r)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="roles[]" value="{{ $r->id }}" class="accent-electric" @checked(in_array($r->id, old('roles', $user->exists ? $user->roles->pluck('id')->all() : [])))> {{ $r->name }}</label>@endforeach</div>
        </fieldset>
        <label class="flex items-center gap-2 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" class="accent-electric" @checked(old('is_active', $user->is_active))> Usuario activo</label>
        <div><button class="btn-primary">Guardar</button></div>
    </form>
</x-layouts.panel>
