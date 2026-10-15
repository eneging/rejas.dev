<?php

namespace App\Models;

use App\Support\Permissions;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = [
        'por_iniciar' => 'Por iniciar',
        'en_ejecucion' => 'En ejecución',
        'pausado' => 'Pausado',
        'entregado' => 'Entregado',
        'finalizado' => 'Finalizado',
        'cancelado' => 'Cancelado',
    ];

    /** Días restantes a partir de los cuales el semáforo pasa a amarillo. */
    public const WARNING_DAYS = 5;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'internal_due_date' => 'date',
            'contract_due_date' => 'date',
            'finished_at' => 'datetime',
            'contract_amount' => 'decimal:2',
            'estimated_cost' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplate::class, 'project_template_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')->withPivot('role');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('position')->orderBy('id');
    }

    public function paymentSchedules(): HasMany
    {
        return $this->hasMany(PaymentSchedule::class)->orderBy('position');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('paid_at');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    /** Proyectos visibles para un usuario: todos con permiso, o solo aquellos en los que participa. */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasPermission(Permissions::PROJECTS_VIEW_ALL)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->whereHas('members', fn (Builder $m) => $m->where('users.id', $user->id))
                ->orWhereHas('tasks', fn (Builder $t) => $t->where('assignee_id', $user->id));
        });
    }

    /**
     * Avance (%) = 100 × Σ pesos de tareas completadas / Σ pesos de tareas computables.
     *
     * - Las tareas canceladas no cuentan en el denominador.
     * - El peso es la estimación en horas cuando todas las tareas computables la tienen;
     *   si alguna carece de estimación se usa peso 1 para todas (no se mezclan unidades).
     * - Sin tareas computables devuelve null ("sin planificación"), nunca un 0 % engañoso.
     */
    public function progress(): ?float
    {
        $tasks = $this->relationLoaded('tasks') ? $this->tasks : $this->tasks()->get();
        $computable = $tasks->where('status', '!=', Task::STATUS_CANCELLED);

        if ($computable->isEmpty()) {
            return null;
        }

        $useHours = $computable->every(fn (Task $t) => (float) $t->estimated_hours > 0);
        $weight = fn (Task $t) => $useHours ? (float) $t->estimated_hours : 1.0;

        $total = $computable->sum($weight);
        $done = $computable->where('status', Task::STATUS_COMPLETED)->sum($weight);

        return round(100 * $done / $total, 1);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['entregado', 'finalizado', 'cancelado'], true);
    }

    /** Fecha objetivo para el semáforo: la meta interna, o la contractual si no hay meta interna. */
    public function targetDate(): ?CarbonInterface
    {
        return $this->internal_due_date ?? $this->contract_due_date;
    }

    /**
     * Semáforo de plazos (independiente de los cobros):
     * verde en plazo; amarillo con WARNING_DAYS días o menos; rojo si venció y no finalizó;
     * "finalizado" / "cancelado" como estados propios.
     */
    public function trafficLight(?CarbonInterface $today = null): string
    {
        if (in_array($this->status, ['entregado', 'finalizado'], true)) {
            return 'finalizado';
        }
        if ($this->status === 'cancelado') {
            return 'cancelado';
        }

        $target = $this->targetDate();
        if (! $target) {
            return 'sin_fecha';
        }

        $today = ($today ?? now())->copy()->startOfDay();
        $daysLeft = (int) $today->diffInDays($target->copy()->startOfDay(), false);

        return match (true) {
            $daysLeft < 0 => 'rojo',
            $daysLeft <= self::WARNING_DAYS => 'amarillo',
            default => 'verde',
        };
    }

    public function daysLeft(?CarbonInterface $today = null): ?int
    {
        $target = $this->targetDate();

        return $target ? (int) ($today ?? now())->copy()->startOfDay()->diffInDays($target->copy()->startOfDay(), false) : null;
    }

    /**
     * Alerta de ritmo: el avance real va por detrás del tiempo consumido en más de $tolerance puntos.
     */
    public function isBehindSchedule(float $tolerance = 20, ?CarbonInterface $today = null): bool
    {
        $progress = $this->progress();
        $target = $this->targetDate();
        if ($this->isClosed() || $progress === null || ! $this->start_date || ! $target) {
            return false;
        }

        $totalDays = max(1, $this->start_date->diffInDays($target, false));
        $elapsed = $this->start_date->diffInDays(($today ?? now())->copy()->startOfDay(), false);
        $elapsedPct = min(100, max(0, 100 * $elapsed / $totalDays));

        return $elapsedPct - $progress > $tolerance;
    }

    /**
     * Sugiere la meta interna reservando el margen de planificación sobre el plazo contractual.
     * Ej.: 20 días con 25 % de margen → meta interna a los 15 días.
     */
    public static function suggestInternalDueDate(CarbonInterface $start, CarbonInterface $contractDue, int $marginPct): CarbonInterface
    {
        $days = $start->diffInDays($contractDue, false);

        return $start->copy()->addDays((int) floor($days * (100 - $marginPct) / 100));
    }

    public function amountPaid(): float
    {
        return (float) ($this->relationLoaded('payments') ? $this->payments->sum('amount') : $this->payments()->sum('amount'));
    }

    public function balance(): float
    {
        return max(0, (float) $this->contract_amount - $this->amountPaid());
    }

    /** Estado de cobro: sin_monto, pendiente, parcial, vencido, pagado. */
    public function paymentStatus(?CarbonInterface $today = null): string
    {
        $amount = (float) $this->contract_amount;
        if ($amount <= 0) {
            return 'sin_monto';
        }

        $paid = $this->amountPaid();
        if ($paid >= $amount) {
            return 'pagado';
        }

        $today = ($today ?? now())->copy()->startOfDay();
        $schedules = $this->relationLoaded('paymentSchedules') ? $this->paymentSchedules : $this->paymentSchedules()->get();
        $dueSoFar = $schedules->filter(fn ($s) => $s->due_date && $s->due_date->lt($today))->sum('amount');

        if ($dueSoFar > $paid) {
            return 'vencido';
        }

        return $paid > 0 ? 'parcial' : 'pendiente';
    }

    public function estimatedMargin(): ?float
    {
        if ($this->estimated_cost === null) {
            return null;
        }

        return (float) $this->contract_amount - (float) $this->estimated_cost - (float) $this->expenses()->sum('amount');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
