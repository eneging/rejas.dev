<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Cargo periódico de una suscripción. Queda "por_cobrar" hasta que se registra un pago real:
 * que venza la mensualidad no la convierte en ingreso recibido.
 */
class SubscriptionCharge extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['period_start' => 'date', 'due_date' => 'date', 'amount' => 'decimal:2'];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function amountPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'por_cobrar' && $this->due_date->isPast();
    }
}
