<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use App\Support\Permissions;

class TaskPolicy
{
    /** El responsable puede actualizar estado, comentar y adjuntar; la gestión completa requiere permiso. */
    public function work(User $user, Task $task): bool
    {
        return $task->assignee_id === $user->id || $user->hasPermission(Permissions::TASKS_MANAGE);
    }

    public function update(User $user, Task $task): bool
    {
        return $user->hasPermission(Permissions::TASKS_MANAGE);
    }
}
