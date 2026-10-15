<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pendiente';

    public const STATUS_IN_PROGRESS = 'en_progreso';

    public const STATUS_COMPLETED = 'completada';

    public const STATUS_CANCELLED = 'cancelada';

    public const STATUSES = [
        self::STATUS_PENDING => 'Pendiente',
        self::STATUS_IN_PROGRESS => 'En progreso',
        self::STATUS_COMPLETED => 'Completada',
        self::STATUS_CANCELLED => 'Cancelada',
    ];

    public const PRIORITIES = ['baja' => 'Baja', 'media' => 'Media', 'alta' => 'Alta'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'estimated_hours' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /** Cambia el estado; completar o reabrir impacta el avance del proyecto de inmediato, sin aprobación. */
    public function transitionTo(string $status): void
    {
        $this->status = $status;
        $this->completed_at = $status === self::STATUS_COMPLETED ? ($this->completed_at ?? now()) : null;
        $this->save();
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
