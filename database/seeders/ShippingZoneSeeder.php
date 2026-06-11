<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingZoneSeeder extends Seeder
{
    public function run(): void
    {
        $freeFrom = (float) config('rodsant.shipping.free_from');

        $zones = [
            ['name' => 'Medellín (local)', 'department' => 'Antioquia', 'city' => 'Medellín', 'cost' => 8000, 'estimated_days' => 1],
            ['name' => 'Bogotá', 'department' => 'Cundinamarca', 'city' => 'Bogotá', 'cost' => 12000, 'estimated_days' => 2],
            ['name' => 'Cali', 'department' => 'Valle del Cauca', 'city' => 'Cali', 'cost' => 12000, 'estimated_days' => 3],
            ['name' => 'Resto del país', 'department' => null, 'city' => null, 'cost' => 15000, 'estimated_days' => 5],
        ];

        foreach ($zones as $zone) {
            ShippingZone::query()->updateOrCreate(
                ['name' => $zone['name']],
                [
                    'department' => $zone['department'],
                    'city' => $zone['city'],
                    'cost' => $zone['cost'],
                    'free_from' => $freeFrom,
                    'estimated_days' => $zone['estimated_days'],
                    'is_active' => true,
                ],
            );
        }
    }
}
