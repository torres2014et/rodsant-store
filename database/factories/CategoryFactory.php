<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'Hoodies & Buzos', 'Camisetas', 'Cargos & Pantalones', 'Chaquetas',
            'Sets', 'Shorts', 'Calzado', 'Accesorios',
        ]);

        return [
            'parent_id' => null,
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1, 9999),
            'image' => null,
            'description' => $this->faker->optional()->sentence(),
            'position' => $this->faker->numberBetween(0, 20),
            'gender' => 'unisex',
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
