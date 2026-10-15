<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectTemplate extends Model
{
    protected $guarded = ['id'];

    public function tasks(): HasMany
    {
        return $this->hasMany(TemplateTask::class)->orderBy('position');
    }
}
