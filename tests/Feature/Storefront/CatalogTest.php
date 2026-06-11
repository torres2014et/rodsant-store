<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use App\Livewire\Storefront\Catalog;
use App\Livewire\Storefront\ProductDetail;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Cart\CartService;
use Database\Seeders\AttributeSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            AttributeSeeder::class,
            CategorySeeder::class,
            SettingSeeder::class,
        ]);
    }

    /**
     * Crea un producto activo con una variante (color/talla) e inventario.
     */
    private function makeProduct(
        string $name,
        string $categorySlug,
        string $color = 'Negro',
        string $size = 'M',
        int $stock = 10,
        int $price = 100000,
        bool $active = true,
        ?int $salePrice = null,
        bool $isNew = false,
    ): Product {
        $category = Category::query()->firstOrCreate(
            ['slug' => $categorySlug],
            ['name' => ucfirst($categorySlug), 'gender' => 'mujer', 'is_active' => true, 'position' => 0],
        );

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => $name,
            'base_price' => $price,
            'sale_price' => $salePrice,
            'is_active' => $active,
            'is_new' => $isNew,
        ]);

        $variant = ProductVariant::query()->create([
            'product_id' => $product->id,
            'sku' => strtoupper(str_replace(' ', '-', $name)),
            'is_active' => true,
        ]);

        $colorValue = AttributeValue::query()->where('value', $color)->firstOrFail();
        $sizeValue = AttributeValue::query()->where('value', $size)->firstOrFail();
        $variant->attributeValues()->attach([$colorValue->id, $sizeValue->id]);

        Inventory::query()->create([
            'product_variant_id' => $variant->id,
            'quantity' => $stock,
            'reserved' => 0,
            'low_stock_threshold' => 3,
        ]);

        return $product->fresh(['category', 'images', 'variants.attributeValues.attribute', 'variants.inventory']);
    }

    private function cart(): CartService
    {
        return app(CartService::class);
    }

    public function test_catalog_lists_active_products_and_hides_inactive(): void
    {
        $this->makeProduct('Hoodie Activo', 'hoodies');
        $this->makeProduct('Camiseta Oculta', 'camisetas', active: false);

        Livewire::test(Catalog::class)
            ->assertOk()
            ->assertSee('Hoodie Activo')
            ->assertDontSee('Camiseta Oculta');
    }

    public function test_category_context_filters_products(): void
    {
        $this->makeProduct('Hoodie Negro', 'hoodies');
        $this->makeProduct('Cargo Gris', 'cargos', color: 'Gris');

        $hoodies = Category::query()->where('slug', 'hoodies')->firstOrFail();

        Livewire::test(Catalog::class, ['category' => $hoodies])
            ->assertSee('Hoodie Negro')
            ->assertDontSee('Cargo Gris')
            ->assertSee('Hoodies'); // título de contexto
    }

    public function test_color_filter_narrows_results(): void
    {
        $this->makeProduct('Hoodie Negro', 'hoodies', color: 'Negro');
        $this->makeProduct('Cargo Gris', 'cargos', color: 'Gris');

        Livewire::test(Catalog::class)
            ->call('toggleColor', 'Negro')
            ->assertSee('Hoodie Negro')
            ->assertDontSee('Cargo Gris')
            ->call('toggleColor', 'Negro') // se quita el filtro
            ->assertSee('Cargo Gris');
    }

    public function test_search_filters_by_name(): void
    {
        $this->makeProduct('Hoodie Negro', 'hoodies');
        $this->makeProduct('Cargo Gris', 'cargos', color: 'Gris');

        Livewire::test(Catalog::class)
            ->set('search', 'Cargo')
            ->assertSee('Cargo Gris')
            ->assertDontSee('Hoodie Negro');
    }

    public function test_sort_by_price_ascending(): void
    {
        $this->makeProduct('Camiseta Barata', 'camisetas', price: 80000);
        $this->makeProduct('Hoodie Caro', 'hoodies', price: 250000);

        Livewire::test(Catalog::class)
            ->set('sort', 'precio_asc')
            ->assertSeeInOrder(['Camiseta Barata', 'Hoodie Caro']);
    }

    public function test_empty_state_when_no_matches(): void
    {
        $this->makeProduct('Hoodie Negro', 'hoodies', color: 'Negro');

        Livewire::test(Catalog::class)
            ->call('toggleColor', 'Hueso') // ningún producto en hueso
            ->assertSee('No encontramos prendas');
    }

    public function test_product_page_renders_with_schema(): void
    {
        $product = $this->makeProduct('Hoodie Editorial', 'hoodies');

        $this->get(route('product.show', $product->slug))
            ->assertOk()
            ->assertSee('Hoodie Editorial')
            ->assertSee('application/ld+json', false)
            ->assertSee('"@type":"Product"', false);
    }

    public function test_inactive_product_page_returns_404(): void
    {
        $product = $this->makeProduct('Oculto', 'hoodies', active: false);

        $this->get(route('product.show', $product->slug))->assertNotFound();
    }

    public function test_product_detail_requires_selection_and_adds_to_cart(): void
    {
        $product = $this->makeProduct('Hoodie Noir', 'hoodies', stock: 5);

        Livewire::test(ProductDetail::class, ['product' => $product])
            ->call('addToCart')
            ->assertHasErrors('variant')
            ->call('selectColor', 'Negro')
            ->call('selectSize', 'M')
            ->call('increment')
            ->call('addToCart')
            ->assertHasNoErrors()
            ->assertSet('added', true)
            ->assertDispatched('cart-updated');

        $this->assertSame(2, $this->cart()->itemCount());
    }

    public function test_product_detail_blocks_when_out_of_stock(): void
    {
        $product = $this->makeProduct('Hoodie Agotado', 'hoodies', stock: 0);

        Livewire::test(ProductDetail::class, ['product' => $product])
            ->call('selectColor', 'Negro')
            ->call('selectSize', 'M')
            ->call('addToCart')
            ->assertHasErrors('variant');

        $this->assertSame(0, $this->cart()->itemCount());
    }
}
