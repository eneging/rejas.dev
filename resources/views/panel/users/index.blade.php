@php use App\Support\Permissions; @endphp
<x-layouts.panel title="Usuarios y permisos">
    <x-slot:actions><a href="{{ route('panel.users.create') }}" class="btn-primary">Nuevo usuario</a></x-slot:actions>
    <div class="card overflow-x-auto p-0">
        <table class="table">
            <thead><tr><th>Nombre</th><th>Correo</th><th>Roles</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            @foreach ($users as $u)
                <tr>
                    <td class="font-medium">{{ $u->name }}</td><td>{{ $u->email }}</td><td>{{ $u->roles->pluck('name')->join(', ') }}</td>
                    <td>@if ($u->is_active)<x-status-badge status="activa" label="Activo" />@else<x-status-badge status="cancelado" label="Inactivo" />@endif</td>
                    <td class="text-right"><a class="link text-sm" href="{{ route('panel.users.edit', $u) }}">Editar</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <section class="card overflow-x-auto p-0">
        <h2 class="px-5 pt-5 font-semibold">Matriz de permisos por rol</h2>
        <table class="table mt-3">
            <thead><tr><th>Permiso</th>@foreach ($roles as $r)<th class="text-center">{{ $r->name }}</th>@endforeach</tr></thead>
            <tbody>
            @foreach (Permissions::all() as $slug => $description)
                <tr><td>{{ $description }}<p class="font-mono text-[11px] text-slate-400">{{ $slug }}</p></td>@foreach ($roles as $r)<td class="text-center">{!! $r->permissions->contains('slug', $slug) ? '<span class="text-emerald-600">●</span>' : '<span class="text-slate-300">○</span>' !!}</td>@endforeach</tr>
            @endforeach
            </tbody>
        </table>
    </section>
</x-layouts.panel>
