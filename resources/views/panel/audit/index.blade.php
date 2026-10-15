<x-layouts.panel title="Auditoría">
    <form class="flex gap-3"><input name="accion" value="{{ request('accion') }}" placeholder="Filtrar por acción (ej. proyecto, pago)" class="input max-w-xs"><button class="btn-secondary">Filtrar</button></form>
    <div class="card overflow-x-auto p-0">
        <table class="table">
            <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Registro</th><th>Detalle</th></tr></thead>
            <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td class="whitespace-nowrap text-xs">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $log->user?->name ?? 'Sistema / web' }}</td>
                    <td class="font-mono text-xs">{{ $log->action }}</td>
                    <td class="text-xs">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                    <td class="max-w-md font-mono text-[11px] break-all text-slate-500">{{ $log->changes ? json_encode($log->changes, JSON_UNESCAPED_UNICODE) : '' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-slate-500">Sin registros.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</x-layouts.panel>
