<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = [
        'borrador' => 'Borrador',
        'enviada' => 'Enviada',
        'aceptada' => 'Aceptada',
        'rechazada' => 'Rechazada',
        'vencida' => 'Vencida',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'valid_until' => 'date',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'estimated_cost' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class)->orderBy('position');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(QuoteVersion::class)->orderByDesc('version');
    }

    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class);
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class);
    }

    public function recalculate(): void
    {
        $subtotal = $this->items()->sum('total');
        $tax = round($subtotal * ((float) $this->tax_rate) / 100, 2);

        $this->forceFill([
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'total' => $subtotal + $tax,
        ])->save();
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['borrador', 'enviada'], true);
    }

    public function isExpired(): bool
    {
        return $this->status === 'enviada' && $this->valid_until && $this->valid_until->isPast();
    }

    public function statusLabel(): string
    {
        return $this->isExpired() ? 'Vencida' : (self::STATUSES[$this->status] ?? $this->status);
    }

    /** @return array<string, mixed> */
    public function snapshot(): array
    {
        return [
            'quote' => $this->only([
                'number', 'title', 'scope', 'conditions', 'currency', 'tax_rate', 'subtotal',
                'tax_amount', 'total', 'delivery_days', 'valid_until', 'status',
            ]),
            'items' => $this->items->map->only(['description', 'quantity', 'unit_price', 'total'])->all(),
        ];
    }
}
