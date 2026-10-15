<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Un gate por permiso: la autorización se evalúa por acción/recurso, no por nombre de rol.
        foreach (array_keys(Permissions::all()) as $permission) {
            Gate::define($permission, fn (User $user) => $user->hasPermission($permission));
        }

        Paginator::defaultView('pagination::tailwind');
    }
}
