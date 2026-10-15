<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

final class Audit
{
    /** @param array<string, mixed> $changes */
    public static function record(string $action, ?Model $subject = null, array $changes = []): AuditLog
    {
        return AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => $subject?->getMorphClass(),
            'auditable_id' => $subject?->getKey(),
            'changes' => $changes ?: null,
            'ip_address' => request()?->ip(),
        ]);
    }

    /**
     * Registra los atributos modificados (antes/después) de un modelo recién guardado.
     */
    public static function changes(string $action, Model $subject): ?AuditLog
    {
        $changes = [];
        foreach ($subject->getChanges() as $key => $new) {
            if ($key === 'updated_at') {
                continue;
            }
            $changes[$key] = ['antes' => $subject->getPrevious()[$key] ?? null, 'despues' => $new];
        }

        return $changes ? self::record($action, $subject, $changes) : null;
    }
}
