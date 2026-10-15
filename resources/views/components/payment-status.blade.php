@props(['project'])
@php
    $status = $project->paymentStatus();
    $label = ['sin_monto' => 'Sin monto', 'pendiente' => 'Pendiente', 'parcial' => 'Parcial', 'vencido' => 'Cobro vencido', 'pagado' => 'Pagado'][$status];
@endphp
<x-status-badge :status="$status" :label="$label" />
