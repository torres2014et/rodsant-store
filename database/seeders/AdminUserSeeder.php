<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Administrador (desarrollador / dueña principal).
        $superAdmin = User::query()->updateOrCreate(
            ['email' => 'admin@rodsantstore.com'],
            [
                'name' => 'Super Admin RodSant',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
        $superAdmin->syncRoles([UserRole::SuperAdmin->value]);

        // Administradora (propietaria de la tienda).
        $admin = User::query()->updateOrCreate(
            ['email' => 'tienda@rodsantstore.com'],
            [
                'name' => 'Administradora RodSant',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
        $admin->syncRoles([UserRole::Admin->value]);
    }
}
