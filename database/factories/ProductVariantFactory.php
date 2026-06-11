<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => strtoupper(Str::random(10)),
            'price_override' => null,
            'barcode' => $this->faker->optional()->ean13(),
            'is_active' => true,
        ];
    }

    /**
     * Crea automáticamente el inventario asociado a la variante.
     */
    public function withInventory(int $quantity = 10): static
    {
        return $this->afterCreating(function (ProductVariant $variant) use ($quantity): void {
            Inventory::factory()->create([
                'product_variant_id' => $variant->id,
                'quantity' => $quantity,
            ]);
        });
    }
}
