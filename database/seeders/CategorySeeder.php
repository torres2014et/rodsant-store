<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Categorías base de la tienda (luxury streetwear, unisex).
     *
     * @var list<string>
     */
    private array $categories = [
        'Hoodies & Buzos',
        'Camisetas',
        'Cargos & Pantalones',
        'Chaquetas & Outerwear',
        'Sets',
        'Shorts',
        'Calzado',
        'Accesorios',
    ];

    public function run(): void
    {
        foreach ($this->categories as $position => $name) {
            Category::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'position' => $position,
                    'gender' => 'unisex',
                    'is_active' => true,
                ],
            );
        }
    }
}
