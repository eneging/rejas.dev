@php
    use App\Models\Project; use App\Models\Task; use App\Support\Money; use App\Support\Permissions as P;
    $user = auth()->user();
    $progress = $project->progress();
    $tasksByAssignee = $project->tasks->groupBy(fn ($t) => $t->assignee?->name ?? 'Sin asignar');
@endphp
<x-layouts.panel :title="$project->name">
    {{-- Cabecera --}}
    <section class="card">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="muted">{{ $project->code }} · <a class="link" href="{{ route('panel.clients.show', $project->client) }}">{{ $project->client->displayName() }}</a>@if($project->quote) · Cotización <a class="link" href="{{ route('panel.quotes.show', $project->quote) }}">{{ $project->quote->number }}</a>@endif</p>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <x-status-badge :status="$project->status" :label="$project->statusLabel()" />
                    <x-traffic-light :project="$project" />
                    @can(P::FINANCE_VIEW)<x-payment-status :project="$project" />@endcan
                    @if ($project->isBehindSchedule())<span class="badge bg-amber-50 text-amber-700">⚠ Avance bajo respecto al tiempo transcurrido</span>@endif
                </div>
            </div>
            <div class="grid grid-cols-3 gap-6 text-sm">
                <div><p class="label">Inicio</p>{{ $project->start_date?->format('d/m/Y') ?? '—' }}</div>
                <div><p class="label">Entrega interna</p>{{ $project->internal_due_date?->format('d/m/Y') ?? '—' }}</div>
                <div><p class="label">Entrega contractual</p>{{ $project->contract_due_date?->format('d/m/Y') ?? '—' }}</div>
            </div>
        </div>
        <div class="mt-5">
            @if ($progress === null)
                <p class="text-sm text-slate-500 italic">Sin planificación: agrega tareas para calcular el avance.</p>
            @else
                <div class="flex items-center justify-between text-sm"><span class="font-medium">Avance</span><span class="font-display text-lg font-semibold tabular-nums">{{ $progress }} %</span></div>
                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800"><div class="h-full rounded-full bg-electric transition-all" style="width: {{ $progress }}%"></div></div>
            @endif
        </div>
        @if ($project->description)<p class="mt-4 text-sm whitespace-pre-line text-slate-600 dark:text-slate-400">{{ $project->description }}</p>@endif
    </section>

    <div class="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
        {{-- Tareas por responsable --}}
        <section class="card space-y-5">
            <h2 class="font-semibold">Tareas</h2>
            @forelse ($tasksByAssignee as $assignee => $tasks)
                <div>
                    <p class="label">{{ $assignee }} <span class="normal-case">({{ $tasks->where('status', Task::STATUS_COMPLETED)->count() }}/{{ $tasks->where('status', '!=', Task::STATUS_CANCELLED)->count() }})</span></p>
                    <ul class="divide-y divide-slate-100 rounded-lg border border-slate-200 dark:divide-slate-800 dark:border-slate-800">
                        @foreach ($tasks as $task)
                            <li class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-3">
                                    @can('work', $task)
                                        <form method="POST" action="{{ route('panel.tasks.status', $task) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $task->isCompleted() ? Task::STATUS_PENDING : Task::STATUS_COMPLETED }}">
                                            <button title="{{ $task->isCompleted() ? 'Reabrir' : 'Completar' }}" @class(['grid h-5 w-5 place-items-center rounded border text-xs', 'border-electric bg-electric text-white' => $task->isCompleted(), 'border-slate-300 hover:border-electric' => ! $task->isCompleted()]) @disabled($task->status === Task::STATUS_CANCELLED)>{{ $task->isCompleted() ? '✓' : '' }}</button>
                                        </form>
                                    @endcan
                                    <span @class(['flex-1 text-sm', 'line-through text-slate-400' => in_array($task->status, [Task::STATUS_COMPLETED, Task::STATUS_CANCELLED])])>{{ $task->title }}</span>
                                    @if ($task->priority === 'alta')<span class="badge bg-red-50 text-red-700">Alta</span>@endif
                                    <span class="text-xs text-slate-500">{{ $task->estimated_hours ? (float) $task->estimated_hours.' h' : '' }} {{ $task->due_date ? '· '.$task->due_date->format('d/m') : '' }}</span>
                                    <x-status-badge :status="$task->status" :label="$task->statusLabel()" />
                                </div>
                                <details class="mt-2 text-sm">
                                    <summary class="cursor-pointer text-xs text-slate-500">Detalle y comentarios · {{ $task->comments->count() }}</summary>
                                    <div class="mt-3 space-y-3">
                                        @if ($task->description)<p class="whitespace-pre-line text-slate-600 dark:text-slate-400">{{ $task->description }}</p>@endif
                                        @can('work', $task)
                                            <form method="POST" action="{{ route('panel.tasks.status', $task) }}" class="flex gap-2">
                                                @csrf @method('PATCH')
                                                <select name="status" class="input w-auto">@foreach (Task::STATUSES as $k => $v)@if($k !== Task::STATUS_CANCELLED || $user->can('update', $task))<option value="{{ $k }}" @selected($task->status === $k)>{{ $v }}</option>@endif @endforeach</select>
                                                <button class="btn-secondary btn-sm">Cambiar estado</button>
                                            </form>
                                        @endcan
                                        <ul class="space-y-2">
                                            @foreach ($task->comments as $comment)
                                                <li class="rounded-lg bg-slate-50 px-3 py-2 dark:bg-slate-800"><span class="text-xs text-slate-500">{{ $comment->user?->name }} · {{ $comment->created_at->format('d/m H:i') }}</span><p class="whitespace-pre-line">{{ $comment->body }}</p></li>
                                            @endforeach
                                        </ul>
                                        @can('work', $task)
                                            <form method="POST" action="{{ route('panel.tasks.comment', $task) }}" enctype="multipart/form-data" class="flex flex-wrap gap-2">
                                                @csrf
                                                <input name="body" placeholder="Agregar comentario" class="input flex-1">
                                                <input type="file" name="file" class="text-xs" aria-label="Adjunto">
                                                <button class="btn-secondary btn-sm">Comentar</button>
                                            </form>
                                        @endcan
                                        @can('update', $task)
                                            <form method="POST" action="{{ route('panel.tasks.update', $task) }}" class="grid gap-2 border-t border-slate-100 pt-3 sm:grid-cols-6 dark:border-slate-800">
                                                @csrf @method('PATCH')
                                                <input name="title" value="{{ $task->title }}" class="input sm:col-span-2" aria-label="Título">
                                                <select name="assignee_id" class="input" aria-label="Responsable"><option value="">Sin asignar</option>@foreach ($users as $u)<option value="{{ $u->id }}" @selected($task->assignee_id === $u->id)>{{ $u->name }}</option>@endforeach</select>
                                                <select name="priority" class="input" aria-label="Prioridad">@foreach (Task::PRIORITIES as $k => $v)<option value="{{ $k }}" @selected($task->priority === $k)>{{ $v }}</option>@endforeach</select>
                                                <input name="estimated_hours" type="number" step="0.5" min="0" value="{{ $task->estimated_hours }}" class="input" placeholder="Horas" aria-label="Horas estimadas">
                                                <input name="due_date" type="date" value="{{ $task->due_date?->toDateString() }}" class="input" aria-label="Fecha">
                                                <input type="hidden" name="description" value="{{ $task->description }}">
                                                <button class="btn-secondary btn-sm sm:col-span-6 sm:justify-self-start">Guardar cambios</button>
                                            </form>
                                        @endcan
                                    </div>
                                </details>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <x-empty>Sin tareas todavía.</x-empty>
            @endforelse

            @can(P::TASKS_MANAGE)
                <form method="POST" action="{{ route('panel.tasks.store', $project) }}" class="grid gap-2 rounded-lg border border-dashed border-slate-300 p-4 sm:grid-cols-6 dark:border-slate-700">
                    @csrf
                    <input name="title" placeholder="Nueva tarea" required class="input sm:col-span-2">
                    <select name="assignee_id" class="input" aria-label="Responsable"><option value="">Responsable</option>@foreach ($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select>
                    <select name="priority" class="input" aria-label="Prioridad">@foreach (Task::PRIORITIES as $k => $v)<option value="{{ $k }}" @selected($k === 'media')>{{ $v }}</option>@endforeach</select>
                    <input name="estimated_hours" type="number" step="0.5" min="0" placeholder="Horas" class="input" aria-label="Horas estimadas">
                    <input name="due_date" type="date" class="input" aria-label="Fecha">
                    <button class="btn-primary btn-sm sm:col-span-6 sm:justify-self-start">Agregar tarea</button>
                </form>
            @endcan
        </section>

        <div class="space-y-6">
            {{-- Fechas y estado --}}
            @can('update', $project)
                <form method="POST" action="{{ route('panel.projects.update', $project) }}" class="card space-y-4">
                    @csrf @method('PATCH')
                    <h2 class="font-semibold">Estado y fechas</h2>
                    <div><label class="label" for="status">Estado operativo</label><select id="status" name="status" class="input">@foreach (Project::STATUSES as $k => $v)<option value="{{ $k }}" @selected($project->status === $k)>{{ $v }}</option>@endforeach</select></div>
                    <div class="grid grid-cols-2 gap-3">
                        <x-field label="Inicio" name="start_date" type="date" :value="$project->start_date?->toDateString()" />
                        <x-field label="Entrega interna" name="internal_due_date" type="date" :value="$project->internal_due_date?->toDateString()" />
                    </div>
                    @can('updateContractDate', $project)
                        <x-field label="Entrega contractual" name="contract_due_date" type="date" :value="$project->contract_due_date?->toDateString()" help="Cambio auditado." />
                    @else
                        <p class="text-xs text-slate-500">La fecha contractual ({{ $project->contract_due_date?->format('d/m/Y') ?? '—' }}) solo puede modificarla un administrador.</p>
                    @endcan
                    <button class="btn-primary">Guardar</button>
                </form>

                <form method="POST" action="{{ route('panel.projects.members', $project) }}" class="card space-y-3">
                    @csrf @method('PUT')
                    <h2 class="font-semibold">Equipo</h2>
                    <div class="flex flex-wrap gap-3">@foreach ($users as $u)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="members[]" value="{{ $u->id }}" class="accent-electric" @checked($project->members->contains($u))> {{ $u->name }}</label>@endforeach</div>
                    <button class="btn-secondary btn-sm">Actualizar equipo</button>
                </form>
            @else
                <section class="card text-sm"><h2 class="font-semibold">Equipo</h2><p class="mt-2">{{ $project->members->pluck('name')->join(', ') ?: '—' }}</p></section>
            @endcan

            {{-- Cobros --}}
            @can(P::FINANCE_VIEW)
                <section class="card space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold">Plan de pagos</h2>
                        <span class="text-sm">{{ Money::format($project->amountPaid(), $project->currency) }} / {{ Money::format($project->contract_amount, $project->currency) }}</span>
                    </div>
                    <ul class="space-y-2 text-sm">
                        @forelse ($project->paymentSchedules as $s)
                            @php $paid = $project->payments->where('payment_schedule_id', $s->id)->sum('amount'); @endphp
                            <li class="flex items-center justify-between gap-2">
                                <span>{{ $s->label }} <span class="text-xs text-slate-500">{{ $s->due_date ? '· vence '.$s->due_date->format('d/m/Y') : '' }}</span></span>
                                <span class="tabular-nums">{{ Money::format($s->amount, $project->currency) }} @if($paid >= $s->amount)<span class="text-emerald-600">✓</span>@elseif($paid > 0)<span class="text-xs text-amber-600">({{ Money::format($paid, $project->currency) }})</span>@endif</span>
                            </li>
                        @empty
                            <li class="text-slate-500">Sin hitos de pago.</li>
                        @endforelse
                    </ul>
                    <p class="text-sm font-medium">Saldo pendiente: {{ Money::format($project->balance(), $project->currency) }}</p>

                    @can(P::PAYMENTS_RECORD)
                        <details class="text-sm" data-repeater>
                            <summary class="cursor-pointer text-xs text-slate-500">Editar hitos de pago</summary>
                            <form method="POST" action="{{ route('panel.payments.schedule', $project) }}" class="mt-3 space-y-2">
                                @csrf
                                <div class="space-y-2" data-repeater-list>
                                    @foreach ($project->paymentSchedules->isEmpty() ? [null] : $project->paymentSchedules as $i => $s)
                                        <div class="grid grid-cols-[1fr_6rem_8rem_auto] gap-2" data-row>
                                            <input name="schedules[{{ $i }}][label]" value="{{ $s?->label ?? 'Adelanto' }}" class="input" aria-label="Hito">
                                            <input name="schedules[{{ $i }}][amount]" value="{{ $s?->amount }}" type="number" step="0.01" class="input" aria-label="Monto">
                                            <input name="schedules[{{ $i }}][due_date]" value="{{ $s?->due_date?->toDateString() }}" type="date" class="input" aria-label="Vencimiento">
                                            <button type="button" data-repeater-remove class="btn-secondary btn-sm">✕</button>
                                        </div>
                                    @endforeach
                                </div>
                                <template><div class="grid grid-cols-[1fr_6rem_8rem_auto] gap-2" data-row><input name="schedules[__INDEX__][label]" class="input" placeholder="Hito"><input name="schedules[__INDEX__][amount]" type="number" step="0.01" class="input"><input name="schedules[__INDEX__][due_date]" type="date" class="input"><button type="button" data-repeater-remove class="btn-secondary btn-sm">✕</button></div></template>
                                <div class="flex gap-2"><button type="button" data-repeater-add class="btn-secondary btn-sm">+ Hito</button><button class="btn-primary btn-sm">Guardar hitos</button></div>
                            </form>
                        </details>

                        <form method="POST" action="{{ route('panel.payments.store', $project) }}" enctype="multipart/form-data" class="grid gap-2 border-t border-slate-100 pt-4 sm:grid-cols-2 dark:border-slate-800">
                            @csrf
                            <p class="font-medium sm:col-span-2">Registrar pago</p>
                            <select name="payment_schedule_id" class="input sm:col-span-2" aria-label="Hito"><option value="">Sin hito / ajuste</option>@foreach ($project->paymentSchedules as $s)<option value="{{ $s->id }}">{{ $s->label }}</option>@endforeach</select>
                            <input name="amount" type="number" step="0.01" min="0.01" placeholder="Importe" required class="input" aria-label="Importe">
                            <input name="paid_at" type="date" value="{{ now()->toDateString() }}" required class="input" aria-label="Fecha de pago">
                            <select name="method" class="input" aria-label="Método">@foreach ($paymentMethods as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
                            <input name="reference" placeholder="N.° de operación" class="input" aria-label="Referencia">
                            <input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png" class="text-xs sm:col-span-2" aria-label="Comprobante">
                            <button class="btn-primary btn-sm sm:col-span-2 sm:justify-self-start">Registrar pago</button>
                        </form>
                    @endcan

                    @if ($project->payments->isNotEmpty())
                        <ul class="space-y-1 border-t border-slate-100 pt-3 text-xs dark:border-slate-800">
                            @foreach ($project->payments as $pay)
                                <li class="flex justify-between"><span>{{ $pay->paid_at->format('d/m/Y') }} · {{ $pay->method }} {{ $pay->reference }} · {{ $pay->recorder?->name }}</span><span class="tabular-nums">{{ Money::format($pay->amount, $pay->currency) }}</span></li>
                            @endforeach
                        </ul>
                    @endif

                    @can(P::FINANCE_VIEW_MARGINS)
                        @if (($margin = $project->estimatedMargin()) !== null)
                            <p class="rounded-lg bg-slate-50 p-3 text-sm dark:bg-slate-800">Utilidad estimada: <strong>{{ Money::format($margin, $project->currency) }}</strong></p>
                        @endif
                    @endcan
                </section>
            @endcan

            {{-- Actividad --}}
            <section class="card">
                <h2 class="font-semibold">Actividad</h2>
                <ul class="mt-3 space-y-2 text-xs">
                    @forelse ($activity as $log)
                        <li><span class="text-slate-500">{{ $log->created_at->format('d/m H:i') }}</span> · {{ $log->user?->name ?? 'Sistema' }} · {{ str_replace(['.', '_'], ' ', $log->action) }}</li>
                    @empty
                        <li class="text-slate-500">Sin actividad.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
</x-layouts.panel>
