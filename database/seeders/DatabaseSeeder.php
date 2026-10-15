<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RolesAndPermissionsSeeder::class, CatalogSeeder::class]);

        $email = env('ADMIN_EMAIL', 'admin@eduarrejas.com');
        $password = env('ADMIN_PASSWORD');
        $admin = User::firstOrCreate(['email' => $email], [
            'name' => env('ADMIN_NAME', 'Eduar Rejas'),
            'password' => $password ?: ($generated = Str::password(16)),
        ]);
        $admin->roles()->syncWithoutDetaching(Role::where('slug', Role::ADMIN)->pluck('id'));

        if (isset($generated)) {
            $this->command?->warn("Administrador creado: {$email} / {$generated} (cámbiala al ingresar)");
        }

        if (app()->environment('local') && env('SEED_DEMO', true)) {
            $this->call(DemoSeeder::class);
        }
    }
}
