@php
    use App\Models\CalendarEvent;
    $colors = ['entrega' => 'bg-electric text-white', 'tarea' => 'bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-100', 'cobro' => 'bg-emerald-100 text-emerald-800', 'reunion' => 'bg-violet-100 text-violet-800', 'hito' => 'bg-amber-100 text-amber-800', 'recordatorio' => 'bg-pink-100 text-pink-800'];
    $step = ['mes' => 'month', 'semana' => 'week', 'dia' => 'day'][$view];
    $q = request()->except(['fecha']);
    $navBase = $view === 'mes' ? $date->copy()->startOfMonth() : $date->copy();
@endphp
<x-layouts.panel title="Calendario">
    <x-slot:actions>
        <a class="btn-secondary" href="{{ route('panel.calendar.export', array_merge(request()->except(['vista', 'fecha']), ['desde' => $from->toDateString(), 'hasta' => $to->toDateString()])) }}">Exportar (Excel CSV)</a>
    </x-slot:actions>

    <div class="flex flex-wrap items-center gap-3">
        <a class="btn-secondary btn-sm" href="{{ route('panel.calendar.index', $q + ['fecha' => $navBase->copy()->sub($step, 1)->toDateString()]) }}">←</a>
        <h2 class="font-display text-lg font-semibold">{{ ucfirst($view === 'mes' ? $date->translatedFormat('F Y') : ($view === 'semana' ? 'Semana del '.$from->format('d/m').' al '.$to->format('d/m/Y') : $date->translatedFormat('l d \d\e F Y'))) }}</h2>
        <a class="btn-secondary btn-sm" href="{{ route('panel.calendar.index', $q + ['fecha' => $navBase->copy()->add($step, 1)->toDateString()]) }}">→</a>
        <a class="link text-sm" href="{{ route('panel.calendar.index', $q + ['fecha' => now()->toDateString()]) }}">Hoy</a>
        <div class="ml-auto inline-flex overflow-hidden rounded-lg border border-slate-300 text-sm dark:border-slate-700">
            @foreach (['mes' => 'Mes', 'semana' => 'Semana', 'dia' => 'Día'] as $k => $v)
                <a href="{{ route('panel.calendar.index', array_merge(request()->query(), ['vista' => $k])) }}" @class(['px-3 py-1.5', 'bg-electric text-white' => $view === $k])>{{ $v }}</a>
            @endforeach
        </div>
    </div>

    <form class="card grid gap-3 sm:grid-cols-5">
        <input type="hidden" name="vista" value="{{ $view }}"><input type="hidden" name="fecha" value="{{ $date->toDateString() }}">
        <select name="tipo" class="input" aria-label="Tipo"><option value="">Todos los tipos</option>@foreach ($kinds as $k => $v)<option value="{{ $k }}" @selected(request('tipo') === $k)>{{ $v }}</option>@endforeach</select>
        <select name="responsable" class="input" aria-label="Responsable"><option value="">Responsable</option>@foreach ($users as $u)<option value="{{ $u->id }}" @selected(request('responsable') == $u->id)>{{ $u->name }}</option>@endforeach</select>
        <select name="cliente" class="input" aria-label="Cliente"><option value="">Cliente</option>@foreach ($clients as $c)<option value="{{ $c->id }}" @selected(request('cliente') == $c->id)>{{ $c->displayName() }}</option>@endforeach</select>
        <select name="proyecto" class="input" aria-label="Proyecto"><option value="">Proyecto</option>@foreach ($projects as $p)<option value="{{ $p->id }}" @selected(request('proyecto') == $p->id)>{{ $p->code }} · {{ $p->name }}</option>@endforeach</select>
        <button class="btn-secondary">Filtrar</button>
    </form>

    @if ($view === 'mes')
        <div class="card overflow-x-auto p-0">
            <div class="grid min-w-[52rem] grid-cols-7 border-b border-slate-200 text-center text-xs font-semibold text-slate-500 uppercase dark:border-slate-800">
                @foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $d)<div class="py-2">{{ $d }}</div>@endforeach
            </div>
            <div class="grid min-w-[52rem] grid-cols-7">
                @for ($day = $from->copy(); $day->lte($to); $day->addDay())
                    @php $key = $day->toDateString(); @endphp
                    <div @class(['min-h-28 border-r border-b border-slate-100 p-1.5 dark:border-slate-800', 'bg-slate-50/60 dark:bg-slate-900/40' => ! $day->isSameMonth($date)])>
                        <a href="{{ route('panel.calendar.index', array_merge(request()->query(), ['vista' => 'dia', 'fecha' => $key])) }}" @class(['inline-grid h-6 w-6 place-items-center rounded-full text-xs', 'bg-electric text-white' => $day->isToday(), 'text-slate-400' => ! $day->isSameMonth($date)])>{{ $day->day }}</a>
                        <ul class="mt-1 space-y-1">
                            @foreach (($entries[$key] ?? collect())->take(4) as $e)
                                <li><a href="{{ $e['url'] ?? '#' }}" class="block truncate rounded px-1.5 py-0.5 text-[11px] {{ $colors[$e['kind']] ?? '' }}" title="{{ $e['title'] }}">{{ isset($e['time']) ? $e['time'].' ' : '' }}{{ $e['title'] }}</a></li>
                            @endforeach
                            @if (($entries[$key] ?? collect())->count() > 4)<li class="text-[11px] text-slate-500">+{{ $entries[$key]->count() - 4 }} más</li>@endif
                        </ul>
                    </div>
                @endfor
            </div>
        </div>
    @else
        <div class="card space-y-5">
            @for ($day = $from->copy(); $day->lte($to); $day->addDay())
                @php $list = $entries[$day->toDateString()] ?? collect(); @endphp
                <div>
                    <h3 @class(['text-sm font-semibold', 'text-electric' => $day->isToday()])>{{ ucfirst($day->translatedFormat('l d/m')) }}</h3>
                    <ul class="mt-2 space-y-1.5">
                        @forelse ($list as $e)
                            <li class="flex items-center gap-3 text-sm"><span class="badge {{ $colors[$e['kind']] ?? '' }}">{{ $kinds[$e['kind']] ?? $e['kind'] }}</span><span class="w-12 text-xs text-slate-500">{{ $e['time'] ?? '' }}</span><a href="{{ $e['url'] ?? '#' }}" class="hover:text-electric">{{ $e['title'] }}</a><span class="ml-auto text-xs text-slate-500">{{ $e['owner'] ?? '' }}</span></li>
                        @empty
                            <li class="text-xs text-slate-400">Sin eventos.</li>
                        @endforelse
                    </ul>
                </div>
            @endfor
        </div>
    @endif

    <form method="POST" action="{{ route('panel.calendar.store') }}" class="card grid gap-3 sm:grid-cols-6">
        @csrf
        <p class="font-semibold sm:col-span-6">Nuevo evento</p>
        <input name="title" placeholder="Título" required class="input sm:col-span-2">
        <select name="type" class="input" aria-label="Tipo">@foreach (CalendarEvent::TYPES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
        <input name="starts_at" type="datetime-local" required class="input sm:col-span-2" aria-label="Inicio">
        <select name="project_id" class="input" aria-label="Proyecto"><option value="">Sin proyecto</option>@foreach ($projects as $p)<option value="{{ $p->id }}">{{ $p->code }}</option>@endforeach</select>
        <label class="flex items-center gap-2 text-sm sm:col-span-3"><input type="checkbox" name="is_private" value="1" class="accent-electric"> Recordatorio personal privado (solo yo lo veo)</label>
        <button class="btn-primary sm:col-span-3 sm:justify-self-end">Agregar</button>
    </form>
    <p class="text-xs text-slate-500">Zona horaria: {{ config('app.timezone') }}.</p>
</x-layouts.panel>
