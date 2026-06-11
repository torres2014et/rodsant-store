<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = ucfirst($this->faker->unique()->words(2, true));
        $basePrice = $this->faker->numberBetween(80, 950) * 1000;

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1, 99999),
            'sku' => strtoupper(Str::random(8)),
            'short_description' => $this->faker->sentence(),
            'description' => $this->faker->paragraphs(2, true),
            'base_price' => $basePrice,
            'sale_price' => null,
            'is_active' => true,
            'is_featured' => $this->faker->boolean(20),
            'is_new' => $this->faker->boolean(30),
            'views' => $this->faker->numberBetween(0, 5000),
            'meta_title' => $name,
            'meta_description' => $this->faker->sentence(),
            'position' => $this->faker->numberBetween(0, 50),
        ];
    }

    public function onSale(): static
    {
        return $this->state(fn (array $attributes): array => [
            'sale_price' => (int) ($attributes['base_price'] * 0.8),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (): array => ['is_featured' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
