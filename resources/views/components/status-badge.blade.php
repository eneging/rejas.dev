@props(['status', 'label' => null])
@php
    $classes = match ($status) {
        'nuevo', 'borrador', 'pendiente', 'por_iniciar', 'por_cobrar' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        'contactado', 'evaluacion', 'enviada', 'en_progreso', 'en_ejecucion', 'parcial' => 'bg-electric-50 text-electric-700 dark:bg-blue-950 dark:text-blue-300',
        'cotizado', 'pausado', 'suspendida', 'entregado' => 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
        'ganado', 'aceptada', 'completada', 'finalizado', 'activa', 'pagado', 'cobrado' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
        'perdido', 'rechazada', 'vencida', 'cancelado', 'cancelada', 'vencido', 'anulado' => 'bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300',
        default => 'bg-slate-100 text-slate-600 dark:bg-slate-800',
    };
@endphp
<span {{ $attributes->class("badge $classes") }}>{{ $label ?? Str::of($status)->replace('_', ' ')->ucfirst() }}</span>
