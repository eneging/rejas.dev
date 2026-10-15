<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function userWithRole(string $role): User
    {
        if (! Role::where('slug', $role)->exists()) {
            $this->seed(RolesAndPermissionsSeeder::class);
        }

        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', $role)->value('id'));

        return $user;
    }
}
