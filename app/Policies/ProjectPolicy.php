<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Support\Permissions;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return Project::visibleTo($user)->whereKey($project->id)->exists();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->hasPermission(Permissions::PROJECTS_MANAGE);
    }

    public function updateContractDate(User $user, Project $project): bool
    {
        return $user->hasPermission(Permissions::PROJECTS_EDIT_CONTRACT_DATE);
    }
}
