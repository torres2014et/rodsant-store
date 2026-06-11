<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $tallas = Attribute::query()->where('slug', 'talla')->first()?->values ?? collect();
        $colores = Attribute::query()->where('slug', 'color')->first()?->values()->take(2)->get() ?? collect();
        $categories = Category::query()->get();

        if ($categories->isEmpty() || $tallas->isEmpty() || $colores->isEmpty()) {
            return;
        }

        // 3 productos demo por categoría, cada uno con variantes talla x color.
        $categories->each(function (Category $category) use ($tallas, $colores): void {
            Product::factory()
                ->count(3)
                ->state(['category_id' => $category->id])
                ->create()
                ->each(function (Product $product) use ($tallas, $colores): void {
                    foreach ($colores as $color) {
                        foreach ($tallas as $talla) {
                            $variant = ProductVariant::query()->create([
                                'product_id' => $product->id,
                                'sku' => strtoupper(Str::random(10)),
                                'is_active' => true,
                            ]);

                            $variant->attributeValues()->attach([$color->id, $talla->id]);

                            Inventory::query()->create([
                                'product_variant_id' => $variant->id,
                                'quantity' => random_int(0, 15),
                                'reserved' => 0,
                                'low_stock_threshold' => 3,
                            ]);
                        }
                    }
                });
        });
    }
}
