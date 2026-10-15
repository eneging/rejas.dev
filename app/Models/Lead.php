<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = [
        'nuevo' => 'Nuevo',
        'contactado' => 'Contactado',
        'evaluacion' => 'En evaluación',
        'cotizado' => 'Cotizado',
        'ganado' => 'Ganado',
        'perdido' => 'Perdido',
    ];

    protected $guarded = ['id'];

    public function consents(): HasMany
    {
        return $this->hasMany(LeadConsent::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
