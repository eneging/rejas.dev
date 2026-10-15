@props(['value'])
@if ($value === null)
    <span class="text-xs text-slate-400 italic">Sin planificación</span>
@else
    <div class="flex items-center gap-2" role="progressbar" aria-valuenow="{{ $value }}" aria-valuemin="0" aria-valuemax="100">
        <div class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
            <div class="h-full rounded-full bg-electric" style="width: {{ min(100, $value) }}%"></div>
        </div>
        <span class="text-xs font-medium tabular-nums">{{ rtrim(rtrim(number_format($value, 1), '0'), '.') }} %</span>
    </div>
@endif
