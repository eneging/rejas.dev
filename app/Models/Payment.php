<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    public const METHODS = [
        'transferencia' => 'Transferencia',
        'yape' => 'Yape',
        'plin' => 'Plin',
        'efectivo' => 'Efectivo',
        'tarjeta' => 'Tarjeta',
        'otro' => 'Otro',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['paid_at' => 'date', 'amount' => 'decimal:2', 'exchange_rate' => 'decimal:4'];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(PaymentSchedule::class, 'payment_schedule_id');
    }

    public function subscriptionCharge(): BelongsTo
    {
        return $this->belongsTo(SubscriptionCharge::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
