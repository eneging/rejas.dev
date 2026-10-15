@props(['project'])
@php
    $light = $project->trafficLight();
    [$label, $classes, $dot] = match ($light) {
        'verde' => ['En plazo', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300', 'bg-emerald-500'],
        'amarillo' => ['Por vencer', 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300', 'bg-amber-500'],
        'rojo' => ['Vencido', 'bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300', 'bg-red-500'],
        'finalizado' => ['Finalizado', 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300', 'bg-slate-400'],
        'cancelado' => ['Cancelado', 'bg-slate-100 text-slate-500 dark:bg-slate-800', 'bg-slate-300'],
        default => ['Sin fecha', 'bg-slate-100 text-slate-500 dark:bg-slate-800', 'bg-slate-300'],
    };
    $days = in_array($light, ['verde', 'amarillo', 'rojo']) ? $project->daysLeft() : null;
@endphp
<span {{ $attributes->class("badge $classes") }} title="Semáforo según fecha {{ $project->internal_due_date ? 'interna' : 'contractual' }}">
    <span class="h-2 w-2 rounded-full {{ $dot }}" aria-hidden="true"></span>
    {{ $label }}@if($days !== null)<span class="opacity-70">· {{ $days < 0 ? abs($days).' d de atraso' : $days.' d' }}</span>@endif
</span>
