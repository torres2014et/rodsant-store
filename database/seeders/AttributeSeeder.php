<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AttributeType;
use App\Models\Attribute;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $talla = Attribute::query()->updateOrCreate(
            ['slug' => 'talla'],
            ['name' => 'Talla', 'type' => AttributeType::Select, 'position' => 1],
        );

        foreach (['S', 'M', 'L', 'XL'] as $i => $value) {
            $talla->values()->updateOrCreate(
                ['value' => $value],
                ['position' => $i, 'meta' => null],
            );
        }

        $color = Attribute::query()->updateOrCreate(
            ['slug' => 'color'],
            ['name' => 'Color', 'type' => AttributeType::Color, 'position' => 2],
        );

        $colors = [
            'Negro' => '#0A0A0A',
            'Blanco' => '#FFFFFF',
            'Gris' => '#8A8A8A',
            'Hueso' => '#F6F4F1',
        ];

        $position = 0;
        foreach ($colors as $name => $hex) {
            $color->values()->updateOrCreate(
                ['value' => $name],
                ['position' => $position++, 'meta' => ['hex' => $hex]],
            );
        }
    }
}
