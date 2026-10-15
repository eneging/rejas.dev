<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Numbering
{
    /**
     * Genera correlativos tipo COT-2026-0007 por prefijo y año.
     *
     * @param  class-string<Model>  $model
     */
    public static function next(string $model, string $prefix, string $column = 'number'): string
    {
        $base = sprintf('%s-%s-', $prefix, now()->format('Y'));

        $last = $model::query()
            ->when(in_array(SoftDeletes::class, class_uses_recursive($model)), fn ($q) => $q->withTrashed())
            ->where($column, 'like', $base.'%')
            ->orderByDesc($column)
            ->value($column);

        $sequence = $last ? ((int) substr($last, strlen($base))) + 1 : 1;

        return $base.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
