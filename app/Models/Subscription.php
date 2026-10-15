<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['activa' => 'Activa', 'suspendida' => 'Suspendida', 'cancelada' => 'Cancelada'];

    public const PERIODICITIES = ['mensual' => 'Mensual', 'anual' => 'Anual (pago adelantado)'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['starts_at' => 'date', 'next_due_date' => 'date', 'amount' => 'decimal:2'];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function charges(): HasMany
    {
        return $this->hasMany(SubscriptionCharge::class)->orderByDesc('period_start');
    }

    public function nextPeriodStart(CarbonInterface $from): CarbonInterface
    {
        return $this->periodicity === 'anual' ? $from->copy()->addYearNoOverflow() : $from->copy()->addMonthNoOverflow();
    }

    public function periodicityLabel(): string
    {
        return self::PERIODICITIES[$this->periodicity] ?? $this->periodicity;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
