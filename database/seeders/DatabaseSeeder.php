<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            AdminUserSeeder::class,
            AttributeSeeder::class,
            SettingSeeder::class,
            ShippingZoneSeeder::class,
            // Datos demo completos (categorías, productos, colecciones,
            // banners, clientes y pedidos) para presentar a la clienta.
            DemoSeeder::class,
        ]);
    }
}
