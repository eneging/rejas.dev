<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Permissions::all() as $slug => $description) {
            Permission::updateOrCreate(['slug' => $slug], ['description' => $description]);
        }

        $names = [Role::ADMIN => 'Administrador', Role::SECRETARY => 'Secretaría', Role::WORKER => 'Trabajador'];

        foreach (Permissions::defaultRoleMatrix() as $slug => $permissions) {
            $role = Role::updateOrCreate(['slug' => $slug], ['name' => $names[$slug]]);
            $role->permissions()->sync(Permission::whereIn('slug', $permissions)->pluck('id'));
        }
    }
}
