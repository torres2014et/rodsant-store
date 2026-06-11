<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 1. Crear todos los permisos (guard web).
        foreach (PermissionEnum::all() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Refresca el caché para que los permisos recién creados sean visibles
        // antes de asignarlos a los roles.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 2. Super Admin: control total (además del bypass por Gate::before).
        $superAdmin = Role::findOrCreate(UserRole::SuperAdmin->value, 'web');
        $superAdmin->syncPermissions(PermissionEnum::all());

        // 3. Administrador: operación diaria, sin configuración crítica ni usuarios.
        $admin = Role::findOrCreate(UserRole::Admin->value, 'web');
        $admin->syncPermissions(PermissionEnum::forAdministrator());

        // 4. Cliente: sin permisos administrativos (sólo acciones de tienda).
        Role::findOrCreate(UserRole::Customer->value, 'web');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
