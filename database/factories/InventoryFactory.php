<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inventory>
 */
class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        return [
            'product_variant_id' => ProductVariant::factory(),
            'quantity' => $this->faker->numberBetween(0, 50),
            'reserved' => 0,
            'low_stock_threshold' => 3,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn (): array => ['quantity' => 0]);
    }
}
